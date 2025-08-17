<?php

namespace App\Http\Middleware;

use App\Models\IpLock;
use App\Models\IpPolicy;
use App\Services\IpLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpPolicy
{
    public function __construct(private IpLockService $locks)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // Whitelist
        $whitelist = IpPolicy::where('type', 'whitelist')->get();
        foreach ($whitelist as $policy) {
            if ($this->cidrMatch($ip, $policy->ip_cidr)) {
                // allowed explicitly
                return $next($request);
            }
        }

        // Blacklist
        $blacklist = IpPolicy::where('type', 'blacklist')->get();
        foreach ($blacklist as $policy) {
            if ($this->cidrMatch($ip, $policy->ip_cidr)) {
                return response()->json([
                    'message' => 'Acesso negado pelo controle de IP (blacklist).'
                ], 403);
            }
        }

        // Temporary locks
        $lock = IpLock::where('ip', $ip)->first();
        if ($this->locks->isLocked($lock)) {
            return response()->json([
                'message' => 'IP temporariamente bloqueado por tentativas falhadas.'
            ], 429);
        }

        return $next($request);
    }

    private function cidrMatch(string $ip, string $cidr): bool
    {
        // supports x.x.x.x/yy
        if (!str_contains($cidr, '/')) {
            // treat as single IP /32
            $cidr .= '/32';
        }
        [$subnet, $mask] = explode('/', $cidr);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false ||
            filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = (int) $mask;
        $maskLong = -1 << (32 - $mask);
        $subnetNet = $subnetLong & $maskLong;
        return ($ipLong & $maskLong) === $subnetNet;
    }
}
