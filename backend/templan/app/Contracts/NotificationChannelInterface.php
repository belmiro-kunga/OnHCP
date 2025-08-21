<?php

namespace App\Contracts;

interface NotificationChannelInterface
{
    /**
     * Send a notification through this channel
     *
     * @param array $recipient Recipient information
     * @param string $subject Notification subject
     * @param string $message Notification message
     * @param array $data Additional data for the notification
     * @return bool Success status
     */
    public function send(array $recipient, string $subject, string $message, array $data = []): bool;

    /**
     * Check if this channel is available/enabled
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get the channel name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get supported notification types for this channel
     *
     * @return array
     */
    public function getSupportedTypes(): array;
}