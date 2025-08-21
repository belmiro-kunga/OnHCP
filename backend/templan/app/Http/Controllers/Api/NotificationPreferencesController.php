<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NotificationPreferencesController extends Controller
{
    /**
     * Obter preferências de notificação do usuário
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'preferences' => $user->notification_preferences,
            ]
        ]);
    }

    /**
     * Atualizar preferências de notificação do usuário
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'simulado_assigned' => 'boolean',
            'simulado_completed' => 'boolean',
            'simulado_result' => 'boolean',
            'simulado_deadline' => 'boolean',
            'email_notifications' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $currentPreferences = $user->notification_preferences;
        
        // Atualizar apenas os campos fornecidos
        $updatedPreferences = array_merge($currentPreferences, $request->only([
            'simulado_assigned',
            'simulado_completed', 
            'simulado_result',
            'simulado_deadline',
            'email_notifications',
            'quiet_hours_enabled',
            'quiet_hours_start',
            'quiet_hours_end'
        ]));
        
        $user->notification_preferences = $updatedPreferences;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferências de notificação atualizadas com sucesso',
            'data' => [
                'preferences' => $user->notification_preferences,
            ]
        ]);
    }

    /**
     * Resetar preferências para os valores padrão
     */
    public function reset(): JsonResponse
    {
        $user = Auth::user();
        $user->notification_preferences = null; // Isso fará com que use os valores padrão
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferências resetadas para os valores padrão',
            'data' => [
                'preferences' => $user->notification_preferences,
            ]
        ]);
    }

    /**
     * Obter configurações disponíveis
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'notification_types' => [
                    'simulado_assigned' => 'Simulado Atribuído',
                    'simulado_completed' => 'Simulado Concluído',
                    'simulado_result' => 'Resultado do Simulado',
                    'simulado_deadline' => 'Lembrete de Prazo',
                ],
                'default_preferences' => [
                    'simulado_assigned' => true,
                    'simulado_completed' => true,
                    'simulado_result' => true,
                    'simulado_deadline' => true,
                    'email_notifications' => true,
                    'quiet_hours_enabled' => false,
                    'quiet_hours_start' => '22:00',
                    'quiet_hours_end' => '08:00',
                ]
            ]
        ]);
    }
}