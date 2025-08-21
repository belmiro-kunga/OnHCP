<?php

namespace App\Contracts;

/**
 * Interface completa para serviços de cache
 * Estende interfaces específicas seguindo o Princípio da Segregação de Interface (ISP)
 */
interface CacheServiceInterface extends CacheClearerInterface, CacheKeyGeneratorInterface
{
    // Esta interface agora herda todos os métodos das interfaces específicas
    // mantendo compatibilidade com código existente
}