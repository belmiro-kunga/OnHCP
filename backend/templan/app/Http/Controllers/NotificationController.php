<?php

namespace App\Http\Controllers;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    private NotificationServiceInterface $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Lista notificações do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'unread_only' => 'boolean',
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
            'type' => ['string', Rule::in([
                'simulado_assigned',
                'simulado_completed',
                'simulado_passed',
                'simulado_failed',
                'simulado_deadline',
            ])],
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $unreadOnly = $request->boolean('unread_only', false);
        $limit = $request->integer('limit', 20);
        $offset = $request->integer('offset', 0);
        $type = $request->string('type');

        // Use NotificationService instead of direct Eloquent queries
        try {
            $notifications = $this->notificationService->getUserNotifications(
                $userId, 
                $unreadOnly, 
                $type, 
                $limit, 
                $offset
            );
            $unreadCount = $this->notificationService->getUnreadCount($userId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar notificações',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'priority' => $notification->priority,
                    'priority_label' => $notification->priority_label,
                    'type_label' => $notification->type_label,
                    'is_read' => $notification->is_read,
                    'age' => $notification->age,
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                ];
            }),
            'unread_count' => $unreadCount,
            'has_more' => $notifications->count() === $limit,
        ]);
    }

    /**
     * Marca uma notificação como lida
     */
    public function markAsRead($id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $success = $this->notificationService->markAsRead((int)$id, $userId);

        if (!$success) {
            return response()->json([
                'message' => 'Notificação não encontrada'
            ], 404);
        }

        return response()->json([
            'message' => 'Notificação marcada como lida',
            'unread_count' => $this->notificationService->getUnreadCount($userId)
        ]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $count = $this->notificationService->markAllAsRead((int)$userId);

        return response()->json([
            'message' => "$count notificações marcadas como lidas",
            'marked_count' => $count,
            'unread_count' => 0
        ]);
    }

    /**
     * Obtém contagem de notificações não lidas
     */
    public function unreadCount(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'unread_count' => 0
            ]);
        }
        $count = $this->notificationService->getUnreadCount((int)$userId);

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Remove uma notificação
     */
    public function destroy($id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        // Use NotificationService instead of direct Eloquent queries
        try {
            $success = $this->notificationService->deleteNotification((int)$id, $userId);
            
            if (!$success) {
                return response()->json([
                    'message' => 'Notificação não encontrada'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover notificação',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Notificação removida',
            'unread_count' => $this->notificationService->getUnreadCount($userId)
        ]);
    }

    /**
     * Obtém estatísticas de notificações
     */
    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'total' => 0,
                'unread' => 0,
                'by_type' => [],
                'by_priority' => [],
            ]);
        }
        
        // Use NotificationService instead of direct Eloquent queries
        try {
            $stats = $this->notificationService->getUserStats($userId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json($stats);
    }
}