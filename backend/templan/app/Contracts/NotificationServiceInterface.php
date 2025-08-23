<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface completa para serviços de notificação
 * Estende interfaces específicas seguindo o Princípio da Segregação de Interface (ISP)
 */
interface NotificationServiceInterface extends NotificationSenderInterface, NotificationChannelManagerInterface
{
    /**
     * Retorna notificações do usuário com filtros/paginação.
     *
     * @param int $userId
     * @param bool $unreadOnly
     * @param string|null $type
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function getUserNotifications(
        int $userId,
        bool $unreadOnly = false,
        ?string $type = null,
        int $limit = 20,
        int $offset = 0
    ): Collection;

    /** Marca uma notificação como lida. */
    public function markAsRead(int $notificationId, int $userId): bool;

    /** Marca todas as notificações do usuário como lidas e retorna a quantidade marcada. */
    public function markAllAsRead(int $userId): int;

    /** Conta notificações não lidas do usuário. */
    public function getUnreadCount(int $userId): int;

    /** Exclui uma notificação do usuário. */
    public function deleteNotification(int $id, int $userId): bool;

    /** Estatísticas agregadas de notificações do usuário. */
    public function getUserStats(int $userId): array;

    /** Cria uma notificação (assinatura mínima para compatibilidade). */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    );
}