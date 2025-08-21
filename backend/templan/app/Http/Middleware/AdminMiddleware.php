<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado'
            ], 401);
        }

        // Verificar se o usuário é administrador
        $user = Auth::user();
        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas administradores podem acessar este recurso.'
            ], 403);
        }

        return $next($request);
    }
}
