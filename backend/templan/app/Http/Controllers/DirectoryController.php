<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class DirectoryController extends Controller
{
    public function show()
    {
        $defaults = [
            'host' => '',       // hostname or IP
            'ip' => '',         // optional explicit IP
            'port' => 389,
            'use_ssl' => false,
            'use_tls' => false,
            'dns_servers' => [], // array of DNS IPs
            'netmask' => '',     // e.g., 255.255.255.0 or /24
            'domain' => '',
            'base_dn' => '',
            'bind_dn' => '',
            // bind_password intentionally omitted from GET
        ];
        $cfg = Setting::getValue('directory.config', $defaults) ?: $defaults;
        // Never expose stored bind_password via GET
        unset($cfg['bind_password']);
        return response()->json(['data' => $cfg]);
    }

    public function update(Request $request)
    {
        $dnsInput = $request->input('dns_servers');
        $dns = [];
        if (is_string($dnsInput)) {
            // allow comma-separated string
            $dns = array_values(array_filter(array_map('trim', explode(',', $dnsInput))));
        } elseif (is_array($dnsInput)) {
            $dns = array_values(array_filter(array_map('trim', $dnsInput)));
        }

        $data = [
            'host' => (string)$request->input('host', ''),
            'ip' => (string)$request->input('ip', ''),
            'port' => (int)$request->input('port', 389),
            'use_ssl' => (bool)$request->input('use_ssl', false),
            'use_tls' => (bool)$request->input('use_tls', false),
            'dns_servers' => $dns,
            'netmask' => (string)$request->input('netmask', ''),
            'domain' => (string)$request->input('domain', ''),
            'base_dn' => (string)$request->input('base_dn', ''),
            'bind_dn' => (string)$request->input('bind_dn', ''),
        ];

        // Persist config (without password)
        Setting::putValue('directory.config', $data);

        // Store password separately if provided (optional)
        if ($request->filled('bind_password')) {
            $encrypted = Crypt::encryptString((string)$request->input('bind_password'));
            Setting::putValue('directory.bind_password', $encrypted);
        }

        $safe = $data; // do not include password
        return response()->json(['data' => $safe]);
    }

    public function testConnection(Request $request)
    {
        if (!class_exists(\LdapRecord\Connection::class)) {
            return response()->json(['data' => [
                'ok' => false,
                'message' => 'Pacote ldaprecord/laravel não instalado/configurado.',
            ]], 503);
        }

        // Merge payload over stored settings
        $stored = Setting::getValue('directory.config', []);
        $payload = $request->all();
        $cfg = array_merge($stored ?: [], is_array($payload) ? $payload : []);

        // Resolve password: prefer request, else stored (decrypt if needed)
        $password = (string)($payload['bind_password'] ?? '');
        if ($password === '') {
            $storedPwd = Setting::getValue('directory.bind_password');
            if (is_string($storedPwd) && $storedPwd !== '') {
                try { $password = Crypt::decryptString($storedPwd); }
                catch (\Throwable $e) { $password = $storedPwd; }
            }
        }

        $host = $cfg['host'] ?? '';
        if (!$host && !empty($cfg['ip'])) { $host = $cfg['ip']; }

        $config = [
            'hosts' => array_filter([$host]),
            'port' => (int)($cfg['port'] ?? 389),
            'base_dn' => (string)($cfg['base_dn'] ?? ''),
            'username' => (string)($cfg['bind_dn'] ?? ''),
            'password' => $password,
            'use_ssl' => (bool)($cfg['use_ssl'] ?? false),
            'use_tls' => (bool)($cfg['use_tls'] ?? false),
            'timeout' => 5,
        ];

        try {
            $connection = new \LdapRecord\Connection($config);
            $connection->connect();
            if (!empty($config['username'])) {
                $ok = $connection->auth()->attempt($config['username'], $config['password'], true);
                if (!$ok) {
                    return response()->json(['data' => [
                        'ok' => false,
                        'message' => 'Conexão estabelecida, mas falha no bind (credenciais).',
                    ]], 200);
                }
            }
            return response()->json(['data' => [
                'ok' => true,
                'message' => 'Conexão e bind (se configurado) bem-sucedidos.',
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [
                'ok' => false,
                'message' => 'Falha na conexão/bind: ' . $e->getMessage(),
            ]], 200);
        }
    }

    public function groups()
    {
        // Stubbed group list; integrate with LDAP to fetch real groups
        return response()->json(['data' => [
            ['name' => 'CN=IT,OU=Groups,DC=example,DC=com'],
            ['name' => 'CN=HR,OU=Groups,DC=example,DC=com'],
            ['name' => 'CN=Finance,OU=Groups,DC=example,DC=com'],
        ]]);
    }
}
