<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DatabaseNotificationChannel implements NotificationChannelInterface
{
    /**
     * Send a notification to the database
     *
     * @param array $recipient Recipient information (must contain 'user_id' key)
     * @param string $subject Notification subject
     * @param string $message Notification message
     * @param array $data Additional data for the notification
     * @return bool Success status
     */
    public function send(array $recipient, string $subject, string $message, array $data = []): bool
    {
        try {
            if (empty($recipient['user_id'])) {
                Log::warning('Database notification failed: No user_id provided', [
                    'recipient' => $recipient,
                    'subject' => $subject
                ]);
                return false;
            }

            $notificationData = [
                'user_id' => $recipient['user_id'],
                'type' => $data['type'] ?? 'general',
                'title' => $subject,
                'message' => $message,
                'data' => json_encode($data),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('notifications')->insert($notificationData);

            Log::info('Database notification created successfully', [
                'user_id' => $recipient['user_id'],
                'type' => $notificationData['type'],
                'title' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Database notification failed', [
                'recipient' => $recipient,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if database channel is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            return DB::connection()->getPdo() !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the channel name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'database';
    }

    /**
     * Get supported notification types for database channel
     *
     * @return array
     */
    public function getSupportedTypes(): array
    {
        return [
            'simulado_completed',
            'certificate_issued',
            'simulado_assigned',
            'reminder',
            'system_notification',
            'achievement',
            'progress_update'
        ];
    }
}