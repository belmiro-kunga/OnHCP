<?php

namespace App\Contracts;

/**
 * Interface completa para serviços de notificação
 * Estende interfaces específicas seguindo o Princípio da Segregação de Interface (ISP)
 */
interface NotificationServiceInterface extends NotificationSenderInterface, NotificationChannelManagerInterface
{
    // Esta interface agora herda todos os métodos das interfaces específicas
    // mantendo compatibilidade com código existente
}