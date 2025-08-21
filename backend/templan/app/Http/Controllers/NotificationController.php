<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
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
                Notification::TYPE_SIMULADO_ASSIGNED,
                Notification::TYPE_SIMULADO_COMPLETED,
                Notification::TYPE_SIMULADO_PASSED,
                Notification::TYPE_SIMULADO_FAILED,
                Notification::TYPE_SIMULADO_DEADLINE,
            ])],
        ]);

        $userId = Auth::id();
        $unreadOnly = $request->boolean('unread_only', false);
        $limit = $request->integer('limit', 20);
        $offset = $request->integer('offset', 0);
        $type = $request->string('type');

        $query = Notification::where('user_id', $userId)
                           ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        if ($type) {
            $query->ofType($type);
        }

        $notifications = $query->skip($offset)->take($limit)->get();
        $unreadCount = $this->notificationService->getUnreadCount($userId);

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
    public function markAsRead(int $id): JsonResponse
    {
        $userId = Auth::id();
        $success = $this->notificationService->markAsRead($id, $userId);

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
        $count = $this->notificationService->markAllAsRead($userId);

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
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Remove uma notificação
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id();
        $notification = Notification::where('id', $id)
                                  ->where('user_id', $userId)
                                  ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notificação não encontrada'
            ], 404);
        }

        $notification->delete();

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
        
        $stats = [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->unread()->count(),
            'by_type' => [],
            'by_priority' => [],
        ];

        // Estatísticas por tipo
        $typeStats = Notification::where('user_id', $userId)
                                ->selectRaw('type, COUNT(*) as count')
                                ->groupBy('type')
                                ->get();
        
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat->type] = $stat->count;
        }

        // Estatísticas por prioridade
        $priorityStats = Notification::where('user_id', $userId)
                                   ->selectRaw('priority, COUNT(*) as count')
                                   ->groupBy('priority')
                                   ->get();
        
        foreach ($priorityStats as $stat) {
            $stats['by_priority'][$stat->priority] = $stat->count;
        }

        return response()->json($stats);
    }
}