<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Setting;

class EmailController extends Controller
{
    public function show()
    {
        $cfg = [
            'host' => (string) (Setting::getValue('email.smtp.host') ?? ''),
            'port' => (int) (Setting::getValue('email.smtp.port') ?? 587),
            'encryption' => (string) (Setting::getValue('email.smtp.encryption') ?? 'tls'), // none|ssl|tls
            'username' => (string) (Setting::getValue('email.smtp.username') ?? ''),
            // password is never returned
            'from_address' => (string) (Setting::getValue('email.from.address') ?? ''),
            'from_name' => (string) (Setting::getValue('email.from.name') ?? ''),
            'auth' => (bool) (Setting::getValue('email.smtp.auth', true)),
            'timeout' => (int) (Setting::getValue('email.smtp.timeout', 10)),
        ];

        return response()->json(['data' => $cfg]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'encryption' => 'nullable|in:none,ssl,tls',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:1024',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'auth' => 'nullable|boolean',
            'timeout' => 'nullable|integer|min:1|max:120',
        ]);

        Setting::putValue('email.smtp.host', (string) $data['host']);
        Setting::putValue('email.smtp.port', (int) ($data['port'] ?? 587));
        Setting::putValue('email.smtp.encryption', (string) ($data['encryption'] ?? 'tls'));
        Setting::putValue('email.smtp.username', (string) ($data['username'] ?? ''));
        if (isset($data['password']) && $data['password'] !== '') {
            Setting::putValue('email.smtp.password', Crypt::encryptString($data['password']));
        }
        Setting::putValue('email.from.address', (string) ($data['from_address'] ?? ''));
        Setting::putValue('email.from.name', (string) ($data['from_name'] ?? ''));
        Setting::putValue('email.smtp.auth', (bool) ($data['auth'] ?? true));
        Setting::putValue('email.smtp.timeout', (int) ($data['timeout'] ?? 10));

        return $this->show();
    }

    public function test(Request $request)
    {
        // Merge stored with overrides from payload
        $stored = [
            'host' => (string) (Setting::getValue('email.smtp.host') ?? ''),
            'port' => (int) (Setting::getValue('email.smtp.port') ?? 587),
            'encryption' => (string) (Setting::getValue('email.smtp.encryption') ?? 'tls'),
            'username' => (string) (Setting::getValue('email.smtp.username') ?? ''),
            'password' => (string) (Setting::getValue('email.smtp.password') ?? ''),
            'auth' => (bool) (Setting::getValue('email.smtp.auth', true)),
            'timeout' => (int) (Setting::getValue('email.smtp.timeout', 10)),
        ];

        $overrides = [
            'host' => $request->input('host'),
            'port' => $request->input('port'),
            'encryption' => $request->input('encryption'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'auth' => $request->input('auth'),
            'timeout' => $request->input('timeout'),
        ];

        $cfg = array_merge($stored, array_filter($overrides, fn($v) => !is_null($v)));

        $password = $cfg['password'] ? (function ($val) {
            // If it's encrypted (typically starts with base64-like), try decrypt; on failure, assume plain override
            try { return Crypt::decryptString($val); } catch (\Throwable $e) { return $val; }
        })($cfg['password']) : '';

        $host = (string) $cfg['host'];
        $port = (int) ($cfg['port'] ?: 587);
        $timeout = (int) ($cfg['timeout'] ?: 10);
        $encryption = (string) ($cfg['encryption'] ?: 'tls');

        if ($host === '') {
            return response()->json(['data' => [ 'ok' => false, 'message' => 'Host SMTP não definido.' ]]);
        }

        $transport = ($encryption === 'ssl') ? 'ssl' : 'tcp';
        $remote = sprintf('%s://%s:%d', $transport, $host, $port);

        $ctxOptions = [];
        if ($encryption === 'ssl') {
            $ctxOptions['ssl'] = [ 'verify_peer' => true, 'verify_peer_name' => true ];
        }

        $context = stream_context_create($ctxOptions);
        $errno = 0; $errstr = '';
        $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            return response()->json(['data' => [ 'ok' => false, 'message' => "Falha ao conectar ao SMTP: $errstr ($errno)" ]]);
        }

        stream_set_timeout($fp, $timeout);
        $read = function() use ($fp) {
            $line = fgets($fp, 512);
            return $line ?: '';
        };
        $write = function($cmd) use ($fp) {
            fwrite($fp, $cmd . "\r\n");
        };

        $greeting = $read();
        if (strpos($greeting, '220') !== 0) {
            fclose($fp);
            return response()->json(['data' => [ 'ok' => false, 'message' => 'Servidor SMTP não respondeu com 220.' ]]);
        }

        $write('EHLO onhcp.local');
        $ehlo = $read();
        if (strpos($ehlo, '250') !== 0) {
            // tente HELO
            $write('HELO onhcp.local');
            $helo = $read();
            if (strpos($helo, '250') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'EHLO/HELO falhou.' ]]);
            }
        }

        // STARTTLS se encryption=tls (o que geralmente significa STARTTLS em 587)
        if ($encryption === 'tls') {
            $write('STARTTLS');
            $resp = $read();
            if (strpos($resp, '220') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'Servidor não aceitou STARTTLS.' ]]);
            }
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'Falha ao iniciar TLS.' ]]);
            }
            // Re-EHLO after STARTTLS
            $write('EHLO onhcp.local');
            $ehlo2 = $read();
            if (strpos($ehlo2, '250') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'EHLO após STARTTLS falhou.' ]]);
            }
        }

        // auth opcional
        if (!empty($cfg['auth']) && !empty($cfg['username'])) {
            $write('AUTH LOGIN');
            $authResp = $read();
            if (strpos($authResp, '334') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'Servidor não aceitou AUTH LOGIN.' ]]);
            }
            $write(base64_encode($cfg['username']));
            $userResp = $read();
            if (strpos($userResp, '334') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'Utilizador SMTP rejeitado.' ]]);
            }
            $write(base64_encode($password));
            $passResp = $read();
            if (strpos($passResp, '235') !== 0) {
                fclose($fp);
                return response()->json(['data' => [ 'ok' => false, 'message' => 'Password SMTP rejeitada.' ]]);
            }
        }

        $write('QUIT');
        fclose($fp);
        return response()->json(['data' => [ 'ok' => true, 'message' => 'Conexão SMTP válida.' ]]);
    }
}
