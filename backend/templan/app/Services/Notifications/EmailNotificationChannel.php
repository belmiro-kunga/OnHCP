<?php

namespace App\Services\Notifications;

use App\Contracts\NotificationChannelInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailNotificationChannel implements NotificationChannelInterface
{
    /**
     * Send a notification through email
     *
     * @param array $recipient Recipient information (must contain 'email' key)
     * @param string $subject Notification subject
     * @param string $message Notification message
     * @param array $data Additional data for the notification
     * @return bool Success status
     */
    public function send(array $recipient, string $subject, string $message, array $data = []): bool
    {
        try {
            if (empty($recipient['email'])) {
                Log::warning('Email notification failed: No email address provided', [
                    'recipient' => $recipient,
                    'subject' => $subject
                ]);
                return false;
            }

            Mail::raw($message, function ($mail) use ($recipient, $subject, $data) {
                $mail->to($recipient['email'])
                     ->subject($subject);
                
                if (!empty($recipient['name'])) {
                    $mail->to($recipient['email'], $recipient['name']);
                }

                // Add any additional headers or attachments from data
                if (!empty($data['cc'])) {
                    $mail->cc($data['cc']);
                }
                
                if (!empty($data['bcc'])) {
                    $mail->bcc($data['bcc']);
                }
            });

            Log::info('Email notification sent successfully', [
                'recipient' => $recipient['email'],
                'subject' => $subject
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Email notification failed', [
                'recipient' => $recipient,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if email channel is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return config('mail.default') !== null;
    }

    /**
     * Get the channel name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'email';
    }

    /**
     * Get supported notification types for email channel
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
            'system_notification'
        ];
    }
}