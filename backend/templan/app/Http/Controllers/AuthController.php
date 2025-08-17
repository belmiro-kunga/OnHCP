<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $data = $request->all();
            Validator::make($data, [
                'email' => 'required|email',
                'password' => 'required|string',
            ])->validate();

            $mode = (string) (Setting::getValue('auth.login_mode', 'password') ?? 'password');
            if ($mode === 'ldap') {
                return $this->loginViaLdap($data['email'], $data['password']);
            }

            // Default: password flow
            /** @var \App\Models\User|null $user */
            $user = User::where('email', $data['email'])->first();
            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            if (!Schema::hasTable('personal_access_tokens')) {
                Log::error('Sanctum table missing: personal_access_tokens not found');
                return response()->json(['message' => 'Auth tokens table missing. Run: php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" && php artisan migrate'], 503);
            }
            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
                'meta' => [
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Login error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    private function loginViaLdap(string $email, string $password)
    {
        // Ensure Sanctum is ready
        if (!Schema::hasTable('personal_access_tokens')) {
            Log::error('Sanctum table missing: personal_access_tokens not found');
            return response()->json(['message' => 'Auth tokens table missing. Run: php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" && php artisan migrate'], 503);
        }

        // Check LDAPRecord availability
        if (!class_exists(\LdapRecord\Connection::class)) {
            return response()->json(['message' => 'LDAP mode requires ldaprecord/laravel. Please install and configure.'], 503);
        }

        try {
            // Build connection from persisted settings
            $cfg = Setting::getValue('directory.config', []);
            if (!is_array($cfg)) { $cfg = []; }
            $host = $cfg['host'] ?? '';
            if (!$host && !empty($cfg['ip'])) { $host = $cfg['ip']; }
            $bindDn = (string)($cfg['bind_dn'] ?? '');
            $bindPwd = '';
            $storedPwd = Setting::getValue('directory.bind_password');
            if (is_string($storedPwd) && $storedPwd !== '') {
                try { $bindPwd = Crypt::decryptString($storedPwd); }
                catch (\Throwable $e) { $bindPwd = $storedPwd; }
            }

            $config = [
                'hosts' => array_filter([$host]),
                'port' => (int)($cfg['port'] ?? 389),
                'base_dn' => (string)($cfg['base_dn'] ?? ''),
                'username' => $bindDn,
                'password' => $bindPwd,
                'use_ssl' => (bool)($cfg['use_ssl'] ?? false),
                'use_tls' => (bool)($cfg['use_tls'] ?? false),
                'timeout' => 5,
            ];

            $conn = new \LdapRecord\Connection($config);
            $conn->connect();
            // Optional admin bind if credentials provided
            if (!empty($bindDn)) {
                $adminOk = $conn->auth()->attempt($bindDn, $bindPwd, true);
                if (!$adminOk) {
                    return response()->json(['message' => 'LDAP bind DN credentials invalid'], 503);
                }
            }

            // Find LDAP user by mail using the constructed connection
            $ldapUser = (new \LdapRecord\Query\Builder($conn))->from('CN=Users')->where('mail', '=', $email)->first();
            if (!$ldapUser) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Attempt bind with user DN and password
            $dn = is_array($ldapUser) ? ($ldapUser['dn'] ?? null) : (method_exists($ldapUser, 'getDn') ? $ldapUser->getDn() : null);
            if (!$dn) {
                return response()->json(['message' => 'User DN not found'], 401);
            }
            $ok = $conn->auth()->attempt($dn, $password, true);
            if (!$ok) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Find or create local user
            $user = User::where('email', $email)->first();
            if (!$user) {
                $displayName = $ldapUser->getFirstAttribute('displayname') ?? $ldapUser->getFirstAttribute('cn') ?? $email;
                $user = User::create([
                    'name' => $displayName,
                    'email' => $email,
                    // Store a random hash to satisfy non-null constraints; password not used in LDAP mode
                    'password' => Hash::make(bin2hex(random_bytes(16))),
                ]);
            }

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
                'meta' => [
                    'token_type' => 'Bearer',
                    'auth' => 'ldap',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('LDAP login error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'LDAP authentication failed'], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['data' => true]);
    }
}
