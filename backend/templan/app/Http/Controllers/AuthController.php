<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['data' => true]);
    }
}
