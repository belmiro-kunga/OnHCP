<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Ms365AuthController extends Controller
{
    protected function b64urlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    protected function discovery(): array
    {
        $tenant = env('MS365_TENANT_ID', 'common');
        $authority = env('MS365_AUTHORITY', "https://login.microsoftonline.com/{$tenant}/v2.0");
        $discoveryUrl = env('MS365_OIDC_DISCOVERY', $authority.'/.well-known/openid-configuration');
        $res = Http::get($discoveryUrl);
        if (!$res->ok()) {
            abort(500, 'Falha ao obter discovery OIDC da Microsoft');
        }
        $data = $res->json();
        return [
            'authority' => $authority,
            'authorization_endpoint' => $data['authorization_endpoint'] ?? null,
            'token_endpoint' => $data['token_endpoint'] ?? null,
            'jwks_uri' => $data['jwks_uri'] ?? null,
            'issuer' => $data['issuer'] ?? null,
        ];
    }

    protected function verifyIdToken(string $jwt, array $disc, string $expectedAud, ?string $expectedNonce = null): array
    {
        // Allow skipping signature verification in testing
        if (env('MS365_SKIP_JWT_VERIFY', false)) {
            $parts = explode('.', $jwt);
            if (count($parts) < 2) abort(401, 'id_token inválido');
            $payload = json_decode($this->b64urlDecode($parts[1]), true);
            if (!$payload) abort(401, 'id_token payload inválido');
            return $payload;
        }

        $parts = explode('.', $jwt);
        if (count($parts) !== 3) abort(401, 'id_token inválido');
        [$h, $p, $s] = $parts;
        $header = json_decode($this->b64urlDecode($h), true) ?: [];
        $payload = json_decode($this->b64urlDecode($p), true) ?: [];
        $signature = $this->b64urlDecode($s);
        $alg = $header['alg'] ?? '';
        $kid = $header['kid'] ?? '';
        if ($alg !== 'RS256') abort(401, 'Algoritmo não suportado');
        if (!$kid) abort(401, 'kid ausente no header');

        // Fetch JWKS
        $jwksUri = $disc['jwks_uri'] ?? null;
        if (!$jwksUri) abort(500, 'jwks_uri não encontrado');
        $jwksRes = Http::get($jwksUri);
        if (!$jwksRes->ok()) abort(500, 'Falha ao obter JWKS');
        $jwks = $jwksRes->json();
        $keys = $jwks['keys'] ?? [];
        $x5c = null;
        foreach ($keys as $k) {
            if (($k['kid'] ?? null) === $kid) {
                $x5c = $k['x5c'][0] ?? null;
                break;
            }
        }
        if (!$x5c) abort(401, 'Chave pública não encontrada para kid');
        // Build PEM cert
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($x5c, 64, "\n") . "-----END CERTIFICATE-----\n";
        $pub = openssl_pkey_get_public($pem);
        if ($pub === false) abort(500, 'Falha ao carregar chave pública');

        $dataToVerify = $h . '.' . $p;
        $ok = openssl_verify($dataToVerify, $signature, $pub, OPENSSL_ALGO_SHA256);
        openssl_free_key($pub);
        if ($ok !== 1) abort(401, 'Assinatura do id_token inválida');

        // Claims validation
        $now = time();
        if (isset($payload['exp']) && $payload['exp'] < $now) abort(401, 'id_token expirado');
        if (isset($payload['nbf']) && $payload['nbf'] > $now) abort(401, 'id_token ainda não válido');
        if (!empty($payload['aud']) && $payload['aud'] !== $expectedAud) abort(401, 'aud inválido');
        if (!empty($disc['issuer']) && !empty($payload['iss']) && strpos($payload['iss'], $disc['issuer']) !== 0) abort(401, 'iss inválido');
        if ($expectedNonce && !empty($payload['nonce']) && $payload['nonce'] !== $expectedNonce) abort(401, 'nonce inválido');

        return $payload;
    }

    public function redirect(Request $request)
    {
        $clientId = env('MS365_CLIENT_ID');
        $redirectUri = env('MS365_REDIRECT_URI');
        $scopes = env('MS365_SCOPES', 'openid profile email offline_access');
        if (!$clientId || !$redirectUri) {
            return response()->json(['message' => 'Configuração MS365 incompleta'], 500);
        }
        $disc = $this->discovery();
        $authEndpoint = $disc['authorization_endpoint'];
        if (!$authEndpoint) {
            return response()->json(['message' => 'authorization_endpoint não encontrado'], 500);
        }
        $state = Str::random(32);
        $nonce = Str::random(32);
        Cache::put('ms365_oidc:'.$state, ['nonce' => $nonce], now()->addMinutes(10));

        $params = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => $scopes,
            'state' => $state,
            'nonce' => $nonce,
            // 'prompt' => 'select_account', // opcional
        ]);
        return redirect()->away($authEndpoint.'?'.$params);
    }

    public function callback(Request $request)
    {
        $code = (string)$request->query('code');
        $state = (string)$request->query('state');
        if (!$code || !$state) {
            return response()->json(['message' => 'Parâmetros inválidos'], 400);
        }
        $cache = Cache::pull('ms365_oidc:'.$state);
        if (!$cache) {
            return response()->json(['message' => 'State inválido/expirado'], 400);
        }
        $expectedNonce = $cache['nonce'] ?? null;

        $clientId = env('MS365_CLIENT_ID');
        $clientSecret = env('MS365_CLIENT_SECRET');
        $redirectUri = env('MS365_REDIRECT_URI');
        if (!$clientId || !$clientSecret || !$redirectUri) {
            return response()->json(['message' => 'Configuração MS365 incompleta'], 500);
        }
        $disc = $this->discovery();
        $tokenEndpoint = $disc['token_endpoint'];
        if (!$tokenEndpoint) {
            return response()->json(['message' => 'token_endpoint não encontrado'], 500);
        }

        $tokenRes = Http::asForm()->post($tokenEndpoint, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);
        if (!$tokenRes->ok()) {
            return response()->json(['message' => 'Falha ao trocar código por tokens', 'detail' => $tokenRes->json()], 401);
        }
        $tokens = $tokenRes->json();
        $idToken = $tokens['id_token'] ?? null;
        if (!$idToken) {
            return response()->json(['message' => 'id_token ausente'], 401);
        }

        // Verificação de assinatura via JWKS e validação de claims
        $payload = $this->verifyIdToken($idToken, $disc, $clientId, $expectedNonce);

        // Extrair atributos
        $email = $payload['email'] ?? $payload['preferred_username'] ?? null;
        $name = $payload['name'] ?? ($email ? explode('@', $email)[0] : 'MS365 User');
        $oid = $payload['oid'] ?? $payload['sub'] ?? null;

        if (!$email) {
            return response()->json(['message' => 'Email não disponível nas claims'], 422);
        }

        // Upsert utilizador local
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            // Definir password aleatória para cumprir constraints (não usada no SSO)
            if (property_exists($user, 'password') || isset($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }
            // Campos opcionais se existirem (ex.: oid)
            if (property_exists($user, 'external_id') || isset($user->external_id)) {
                $user->external_id = $oid;
            }
            $user->save();
        }

        // Criar token (Sanctum)
        if (!method_exists($user, 'createToken')) {
            return response()->json(['message' => 'Sanctum não configurado'], 500);
        }
        $token = $user->createToken('ms365')->plainTextToken;

        // Optional FE redirect
        $frontend = env('FRONTEND_APP_URL');
        $shouldRedirect = (bool)$request->query('redirect', false) || env('MS365_CALLBACK_REDIRECT', false);
        if ($frontend && $shouldRedirect) {
            $sep = str_contains($frontend, '?') ? '&' : '?';
            return redirect()->away($frontend . $sep . http_build_query(['token' => $token]));
        }

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => method_exists($user, 'roles') ? $user->roles : ['guest'],
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Logout efetuado']);
    }
}
