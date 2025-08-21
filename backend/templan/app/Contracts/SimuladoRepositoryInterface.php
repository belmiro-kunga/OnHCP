<?php

namespace App\Contracts;

/**
 * Interface completa para repositório de simulados
 * Estende interfaces específicas seguindo o Princípio da Segregação de Interface (ISP)
 */
interface SimuladoRepositoryInterface extends SimuladoReaderInterface, SimuladoWriterInterface
{
    // Esta interface agora herda todos os métodos das interfaces específicas
    // mantendo compatibilidade com código existente
}