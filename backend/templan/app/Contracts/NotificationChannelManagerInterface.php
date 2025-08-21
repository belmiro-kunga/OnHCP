<?php

namespace App\Contracts;

interface NotificationChannelManagerInterface
{
    /**
     * Add a notification channel
     *
     * @param NotificationChannelInterface $channel
     * @return void
     */
    public function addChannel(NotificationChannelInterface $channel): void;

    /**
     * Remove a notification channel
     *
     * @param string $channelName
     * @return void
     */
    public function removeChannel(string $channelName): void;

    /**
     * Get available channels
     *
     * @return array
     */
    public function getAvailableChannels(): array;

    /**
     * Check if a channel is available
     *
     * @param string $channelName
     * @return bool
     */
    public function hasChannel(string $channelName): bool;
}