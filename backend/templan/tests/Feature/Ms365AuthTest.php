<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Ms365AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Basic env config
        Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        putenv('MS365_TENANT_ID=common');
        putenv('MS365_CLIENT_ID=test-client-id');
        putenv('MS365_CLIENT_SECRET=test-secret');
        putenv('MS365_REDIRECT_URI=http://localhost/api/auth/sso/ms365/callback');
        putenv('MS365_SCOPES=openid profile email offline_access');
        putenv('FRONTEND_APP_URL=http://localhost:5173');
    }

    public function testRedirectBuildsAuthorizationUrl()
    {
        Http::fake([
            'login.microsoftonline.com/*/.well-known/openid-configuration' => Http::response([
                'authorization_endpoint' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            ], 200),
        ]);

        $res = $this->get('/api/auth/sso/ms365/redirect');
        $res->assertStatus(302);
        $location = $res->headers->get('Location');
        $this->assertStringContainsString('https://login.microsoftonline.com/common/oauth2/v2.0/authorize', $location);
        $this->assertStringContainsString('client_id=test-client-id', $location);
        $this->assertStringContainsString('response_type=code', $location);
        $this->assertStringContainsString('redirect_uri=' . urlencode('http://localhost/api/auth/sso/ms365/callback'), $location);
        $this->assertStringContainsString('scope=' . urlencode('openid profile email offline_access'), $location);
        $this->assertStringContainsString('state=', $location);
        $this->assertStringContainsString('nonce=', $location);
    }

    public function testCallbackWithSkipVerification()
    {
        // Arrange discovery and token endpoint
        Http::fake([
            'login.microsoftonline.com/*/.well-known/openid-configuration' => Http::response([
                'authorization_endpoint' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                'token_endpoint' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            ], 200),
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'at',
                'id_token' => $this->makeUnsignedJwt([
                    'aud' => 'test-client-id',
                    'email' => 'admin@demo.com',
                    'name' => 'Admin Demo',
                    'nonce' => 'NONCE123',
                    'exp' => time() + 600,
                ]),
                'refresh_token' => 'rt',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
        ]);

        // Seed state/nonce
        $state = 'STATE123';
        Cache::put('ms365_oidc:'.$state, ['nonce' => 'NONCE123'], now()->addMinutes(10));

        // Skip signature verify for tests
        putenv('MS365_SKIP_JWT_VERIFY=true');

        $res = $this->get('/api/auth/sso/ms365/callback?code=CODE123&state='.$state);
        $res->assertStatus(200);
        $json = $res->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('token', $json['data']);
        $this->assertArrayHasKey('user', $json['data']);
        $this->assertEquals('admin@demo.com', $json['data']['user']['email']);
    }

    public function testCallbackInvalidState()
    {
        Http::fake([
            'login.microsoftonline.com/*/.well-known/openid-configuration' => Http::response([
                'token_endpoint' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            ], 200),
        ]);
        $res = $this->get('/api/auth/sso/ms365/callback?code=ANY&state=INVALID');
        $res->assertStatus(400);
    }

    private function makeUnsignedJwt(array $claims): string
    {
        $header = base64_encode(json_encode(['alg' => 'none', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($claims));
        // Convert to URL-safe base64
        $b64url = function ($s) {
            return rtrim(strtr($s, '+/', '-_'), '=');
        };
        return $b64url($header) . '.' . $b64url($payload) . '.';
    }
}
