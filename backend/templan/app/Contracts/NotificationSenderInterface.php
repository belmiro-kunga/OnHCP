<?php

namespace App\Contracts;

interface NotificationSenderInterface
{
    /**
     * Send a notification using available channels
     *
     * @param array $recipient Recipient information
     * @param string $type Notification type
     * @param string $subject Notification subject
     * @param string $message Notification message
     * @param array $data Additional data
     * @param array $channels Specific channels to use (optional)
     * @return array Results from each channel
     */
    public function send(
        array $recipient,
        string $type,
        string $subject,
        string $message,
        array $data = [],
        array $channels = []
    ): array;
}