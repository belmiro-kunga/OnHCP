<?php

namespace App\Contracts;

use App\Models\Simulado;
use App\Models\SimuladoQuestion;
use Illuminate\Database\Eloquent\Collection;

interface SimuladoQuestionServiceInterface
{
    /**
     * Cria questões para um simulado
     */
    public function createQuestions(Simulado $simulado, array $questionsData): Collection;

    /**
     * Atualiza questões de um simulado
     */
    public function updateQuestions(Simulado $simulado, array $questionsData): Collection;

    /**
     * Remove todas as questões de um simulado
     */
    public function deleteAllQuestions(Simulado $simulado): bool;

    /**
     * Valida dados de uma questão
     */
    public function validateQuestionData(array $questionData): array;

    /**
     * Ordena questões de um simulado
     */
    public function reorderQuestions(Simulado $simulado, array $questionIds): bool;

    /**
     * Duplica questões de um simulado para outro
     */
    public function duplicateQuestions(Simulado $sourceSimulado, Simulado $targetSimulado): Collection;
}