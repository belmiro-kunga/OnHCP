<?php

namespace App\Contracts;

use App\Models\Simulado;

interface SimuladoWriterInterface
{
    /**
     * Cria um novo simulado
     */
    public function create(array $data): Simulado;

    /**
     * Atualiza um simulado existente
     */
    public function update(Simulado $simulado, array $data): Simulado;

    /**
     * Remove um simulado
     */
    public function delete(Simulado $simulado): bool;
}