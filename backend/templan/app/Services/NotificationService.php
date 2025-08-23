<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Simulado;
use App\Models\SimuladoAttempt;
use App\Events\NotificationSent;
use App\Contracts\NotificationServiceInterface;
use App\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class NotificationService implements NotificationServiceInterface
{
    /**
     * @var array<string, NotificationChannelInterface>
     */
    private array $channels = [];

    public function __construct()
    {
        // Initialize with default channels
        $this->addChannel(app(\App\Services\Notifications\EmailNotificationChannel::class));
        $this->addChannel(app(\App\Services\Notifications\DatabaseNotificationChannel::class));
    }
    /**
     * Send a notification using available channels
     */
    public function send(
        array $recipient,
        string $type,
        string $subject,
        string $message,
        array $data = [],
        array $channels = []
    ): array {
        $results = [];
        $channelsToUse = empty($channels) ? $this->channels : array_intersect_key($this->channels, array_flip($channels));

        foreach ($channelsToUse as $channel) {
            if ($channel->isAvailable() && in_array($type, $channel->getSupportedTypes())) {
                $success = $channel->send($recipient, $subject, $message, $data);
                $results[$channel->getName()] = $success;
            }
        }

        return $results;
    }

    /**
     * Add a notification channel
     */
    public function addChannel(NotificationChannelInterface $channel): void
    {
        $this->channels[$channel->getName()] = $channel;
    }

    /**
     * Remove a notification channel
     */
    public function removeChannel(string $channelName): void
    {
        unset($this->channels[$channelName]);
    }

    /**
     * Get available channels
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->channels);
    }

    /**
     * Check if a channel is available
     */
    public function hasChannel(string $channelName): bool
    {
        return isset($this->channels[$channelName]);
    }

    /**
     * Envia um email simples para a lista de administradores definida em NOTIFY_ADMIN_EMAILS
     * NOTIFY_ADMIN_EMAILS=admin1@example.com,admin2@example.com
     */
    public function notifyAdmins(string $subject, string $body): void
    {
        $list = trim((string) env('NOTIFY_ADMIN_EMAILS', ''));
        if ($list === '') {
            return; // sem destinatários configurados
        }
        $emails = array_filter(array_map('trim', explode(',', $list)));
        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            // Envio simples em texto puro para evitar criar Mailables agora
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        }
    }

    // ====== Approvals ======
    /** Pedido de aprovação criado */
    public function approvalRequested(string $requestId, string $requester, string $target, string $changesSummary): void
    {
        $subject = sprintf('[Approvals] Pedido #%s criado por %s', $requestId, $requester);
        $body = "Pedido de aprovação criado.\nRequester: $requester\nTarget: $target\nAlterações: $changesSummary\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }
    /** Pedido aprovado */
    public function approvalApproved(string $requestId, string $approver, string $target): void
    {
        $subject = sprintf('[Approvals] Pedido #%s APROVADO', $requestId);
        $body = "Pedido aprovado.\nAprovador: $approver\nTarget: $target\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }
    /** Pedido rejeitado */
    public function approvalRejected(string $requestId, string $approver, string $target, ?string $reason = null): void
    {
        $subject = sprintf('[Approvals] Pedido #%s REJEITADO', $requestId);
        $body = "Pedido rejeitado.\nAprovador: $approver\nTarget: $target\nMotivo: " . ($reason ?: '-') . "\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }

    // ====== Delegations ======
    /** Delegação criada */
    public function delegationCreated(string $delegationId, string $delegator, string $delegatee, string $scope, string $startsAt, string $endsAt): void
    {
        $subject = sprintf('[Delegations] Delegação #%s criada', $delegationId);
        $body = "Delegação criada.\nDelegador: $delegator\nDelegado: $delegatee\nEscopo: $scope\nInício: $startsAt\nFim: $endsAt\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }
    /** Delegação expirada */
    public function delegationExpired(string $delegationId, string $delegator, string $delegatee, string $endsAt): void
    {
        $subject = sprintf('[Delegations] Delegação #%s expirada', $delegationId);
        $body = "Delegação expirada.\nDelegador: $delegator\nDelegado: $delegatee\nFim: $endsAt\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }
    /** Delegação revogada */
    public function delegationRevoked(string $delegationId, string $revoker, string $delegator, string $delegatee): void
    {
        $subject = sprintf('[Delegations] Delegação #%s revogada', $delegationId);
        $body = "Delegação revogada.\nRevogador: $revoker\nDelegador: $delegator\nDelegado: $delegatee\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }

    // ====== Licenses ======
    /** Licença atribuída */
    public function licenseAssigned(string $licenseId, string $product, string $userEmail): void
    {
        $subject = sprintf('[Licenses] Licença %s atribuída a %s', $product, $userEmail);
        $body = "Licença atribuída.\nProduto: $product\nUtilizador: $userEmail\nLicense ID: $licenseId";
        $this->notifyAdmins($subject, $body);
    }
    /** Licença removida */
    public function licenseUnassigned(string $licenseId, string $product, string $userEmail): void
    {
        $subject = sprintf('[Licenses] Licença %s removida de %s', $product, $userEmail);
        $body = "Licença removida.\nProduto: $product\nUtilizador: $userEmail\nLicense ID: $licenseId";
        $this->notifyAdmins($subject, $body);
    }
    /** Limite de licenças atingido */
    public function licenseLimitReached(string $product, int $seatsTotal): void
    {
        $subject = sprintf('[Licenses] Limite atingido para %s', $product);
        $body = "Limite de licenças atingido.\nProduto: $product\nVagas totais: $seatsTotal";
        $this->notifyAdmins($subject, $body);
    }

    // ====== Simulados ======
    
    /**
     * Cria uma notificação para um usuário específico
     */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = Notification::PRIORITY_NORMAL,
        \DateTime $scheduledFor = null
    ): Notification {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'scheduled_for' => $scheduledFor,
        ]);

        // Disparar evento de broadcast para notificações em tempo real
        $user = User::find($userId);
        if ($user) {
            broadcast(new NotificationSent($notification, $user));
        }

        return $notification;
    }

    /**
     * Notifica usuário sobre simulado atribuído
     */
    public function simuladoAssigned(User $user, Simulado $simulado, array $assignmentData = []): ?Notification
    {
        // Verificar se o usuário quer receber este tipo de notificação
        if (!$user->wantsNotification('simulado_assigned')) {
            return null;
        }

        $title = 'Novo Simulado Atribuído';
        $message = sprintf(
            'O simulado "%s" foi atribuído para você. Você tem %d tentativas para completá-lo.',
            $simulado->title,
            $assignmentData['max_attempts'] ?? $simulado->max_attempts
        );
        
        $data = [
            'simulado_id' => $simulado->id,
            'simulado_title' => $simulado->title,
            'max_attempts' => $assignmentData['max_attempts'] ?? $simulado->max_attempts,
            'due_at' => $assignmentData['due_at'] ?? null,
        ];

        $notification = $this->createNotification(
            $user->id,
            Notification::TYPE_SIMULADO_ASSIGNED,
            $title,
            $message,
            $data,
            Notification::PRIORITY_HIGH
        );

        if ($user->wantsEmailNotifications() && !$user->isInQuietHours()) {
            $this->sendEmailNotification($user, $notification);
        }
        
        return $notification;
    }

    /**
     * Notifica usuário sobre resultado do simulado
     */
    public function simuladoResult(User $user, Simulado $simulado, SimuladoAttempt $attempt): ?Notification
    {
        // Verificar se o usuário quer receber este tipo de notificação
        if (!$user->wantsNotification('simulado_result')) {
            return null;
        }

        $passed = $attempt->passed;
        $title = $passed ? 'Simulado Aprovado!' : 'Resultado do Simulado';
        $message = sprintf(
            'Você %s no simulado "%s" com nota %d%%. %s',
            $passed ? 'foi aprovado' : 'não atingiu a nota mínima',
            $simulado->title,
            $attempt->score,
            $passed ? 'Parabéns!' : 'Você pode tentar novamente se ainda tiver tentativas disponíveis.'
        );
        
        $data = [
            'simulado_id' => $simulado->id,
            'simulado_title' => $simulado->title,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'passed' => $passed,
            'min_score' => $simulado->min_score,
        ];

        $type = $passed ? Notification::TYPE_SIMULADO_PASSED : Notification::TYPE_SIMULADO_FAILED;
        $priority = $passed ? Notification::PRIORITY_NORMAL : Notification::PRIORITY_HIGH;

        $notification = $this->createNotification(
            $user->id,
            $type,
            $title,
            $message,
            $data,
            $priority
        );

        if ($user->wantsEmailNotifications() && !$user->isInQuietHours()) {
            $this->sendEmailNotification($user, $notification);
        }
        
        return $notification;
    }

    /**
     * Notifica usuário sobre prazo próximo do simulado
     */
    public function simuladoDeadlineReminder(User $user, Simulado $simulado, \DateTime $dueDate, int $daysRemaining): ?Notification
    {
        // Verificar se o usuário quer receber este tipo de notificação
        if (!$user->wantsNotification('simulado_deadline')) {
            return null;
        }

        $title = 'Lembrete: Prazo do Simulado';
        $message = sprintf(
            'O simulado "%s" vence em %d %s (%s). Não esqueça de completá-lo!',
            $simulado->title,
            $daysRemaining,
            $daysRemaining === 1 ? 'dia' : 'dias',
            $dueDate->format('d/m/Y')
        );
        
        $data = [
            'simulado_id' => $simulado->id,
            'simulado_title' => $simulado->title,
            'due_date' => $dueDate->format('Y-m-d H:i:s'),
            'days_remaining' => $daysRemaining,
        ];

        $priority = $daysRemaining <= 1 ? Notification::PRIORITY_URGENT : Notification::PRIORITY_HIGH;

        $notification = $this->createNotification(
            $user->id,
            Notification::TYPE_SIMULADO_DEADLINE,
            $title,
            $message,
            $data,
            $priority
        );

        if ($user->wantsEmailNotifications() && !$user->isInQuietHours()) {
            $this->sendEmailNotification($user, $notification);
        }
        
        return $notification;
    }

    /**
     * Notifica usuário sobre simulado concluído
     */
    public function simuladoCompleted(User $user, Simulado $simulado, SimuladoAttempt $attempt): ?Notification
    {
        // Verificar se o usuário quer receber este tipo de notificação
        if (!$user->wantsNotification('simulado_completed')) {
            return null;
        }

        $title = 'Simulado Concluído';
        $message = sprintf(
            'Você concluiu o simulado "%s". Aguarde o resultado.',
            $simulado->title
        );
        
        $data = [
            'simulado_id' => $simulado->id,
            'simulado_title' => $simulado->title,
            'attempt_id' => $attempt->id,
            'submitted_at' => $attempt->submitted_at?->format('Y-m-d H:i:s'),
        ];

        $notification = $this->createNotification(
            $user->id,
            Notification::TYPE_SIMULADO_COMPLETED,
            $title,
            $message,
            $data,
            Notification::PRIORITY_NORMAL
        );

        if ($user->wantsEmailNotifications() && !$user->isInQuietHours()) {
            $this->sendEmailNotification($user, $notification);
        }
        
        return $notification;
    }

    /**
     * Envia notificação por email
     */
    private function sendEmailNotification(User $user, Notification $notification): void
    {
        try {
            $template = $this->getEmailTemplate($notification->type);
            $data = $this->prepareEmailData($user, $notification);
            
            if ($template) {
                Mail::send($template, $data, function ($message) use ($user, $notification) {
                    $message->to($user->email, $user->name)
                           ->subject($notification->title);
                });
            } else {
                // Fallback para template simples
                Mail::raw($notification->message, function ($message) use ($user, $notification) {
                    $message->to($user->email, $user->name)
                           ->subject($notification->title);
                });
            }
            
            $notification->markEmailAsSent();
        } catch (\Throwable $e) {
            // Log error but don't break the flow
            Log::error('Failed to send notification email', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtém o template de email baseado no tipo de notificação
     */
    private function getEmailTemplate(string $type): ?string
    {
        $templates = [
            Notification::TYPE_SIMULADO_ASSIGNED => 'emails.simulado-assigned',
            Notification::TYPE_SIMULADO_DEADLINE => 'emails.simulado-deadline',
            Notification::TYPE_SIMULADO_PASSED => 'emails.simulado-result',
            Notification::TYPE_SIMULADO_FAILED => 'emails.simulado-result',
            Notification::TYPE_SIMULADO_COMPLETED => 'emails.simulado-completed',
        ];

        return $templates[$type] ?? null;
    }

    /**
     * Prepara os dados para o template de email
     */
    private function prepareEmailData(User $user, Notification $notification): array
    {
        $data = [
            'user' => $user,
            'notification' => $notification,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data,
        ];

        // Adicionar dados específicos baseados no tipo
        switch ($notification->type) {
            case Notification::TYPE_SIMULADO_ASSIGNED:
                $data['simulado'] = $this->getSimuladoFromNotification($notification);
                $data['dueDate'] = $notification->data['due_at'] ?? null;
                $data['maxAttempts'] = $notification->data['max_attempts'] ?? null;
                break;

            case Notification::TYPE_SIMULADO_DEADLINE:
                $data['simulado'] = $this->getSimuladoFromNotification($notification);
                $data['dueDate'] = $notification->data['due_date'] ?? null;
                $data['daysRemaining'] = $notification->data['days_remaining'] ?? 0;
                break;

            case Notification::TYPE_SIMULADO_PASSED:
            case Notification::TYPE_SIMULADO_FAILED:
                $data['simulado'] = $this->getSimuladoFromNotification($notification);
                $data['score'] = $notification->data['score'] ?? 0;
                $data['passed'] = $notification->data['passed'] ?? false;
                $data['minScore'] = $notification->data['min_score'] ?? 0;
                $data['attempt'] = $this->getAttemptFromNotification($notification);
                break;

            case Notification::TYPE_SIMULADO_COMPLETED:
                $data['simulado'] = $this->getSimuladoFromNotification($notification);
                $data['submittedAt'] = $notification->data['submitted_at'] ?? null;
                $data['attempt'] = $this->getAttemptFromNotification($notification);
                break;
        }

        return $data;
    }

    /**
     * Obtém o simulado a partir dos dados da notificação
     */
    private function getSimuladoFromNotification(Notification $notification)
    {
        $simuladoId = $notification->data['simulado_id'] ?? null;
        return $simuladoId ? Simulado::find($simuladoId) : null;
    }

    /**
     * Obtém a tentativa a partir dos dados da notificação
     */
    private function getAttemptFromNotification(Notification $notification)
    {
        $attemptId = $notification->data['attempt_id'] ?? null;
        return $attemptId ? SimuladoAttempt::find($attemptId) : null;
    }

    /**
     * Marca notificação como lida
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
                                  ->where('user_id', $userId)
                                  ->first();
        
        return $notification ? $notification->markAsRead() : false;
    }

    /**
     * Marca todas as notificações do usuário como lidas
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
                         ->whereNull('read_at')
                         ->update(['read_at' => now()]);
    }



    /**
     * Conta notificações não lidas do usuário
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)->unread()->count();
    }

    /**
     * Remove notificações antigas
     */
    public function cleanupOldNotifications(int $daysOld = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))
                         ->delete();
    }

    /**
     * Get user notifications with filters
     */
    public function getUserNotifications(
        int $userId, 
        bool $unreadOnly = false, 
        ?string $type = null, 
        int $limit = 20, 
        int $offset = 0
    ): Collection {
        $query = Notification::where('user_id', $userId)
                           ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        if ($type) {
            $query->ofType($type);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(int $id, int $userId): bool
    {
        $notification = Notification::where('id', $id)
                                  ->where('user_id', $userId)
                                  ->first();

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * Get user notification statistics
     */
    public function getUserStats(int $userId): array
    {
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

        return $stats;
    }
}
