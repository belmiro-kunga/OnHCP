<?php

namespace App\Contracts;

interface ScoringStrategyInterface
{
    /**
     * Calculate the score for a simulado attempt
     *
     * @param array $answers User's answers
     * @param array $questions Simulado questions with correct answers
     * @return array Score details including total score, correct answers, etc.
     */
    public function calculateScore(array $answers, array $questions): array;

    /**
     * Determine if the attempt passed based on the score
     *
     * @param array $scoreDetails Score details from calculateScore
     * @param float $passingScore Minimum score to pass
     * @return bool
     */
    public function hasPassed(array $scoreDetails, float $passingScore): bool;

    /**
     * Get the strategy name
     *
     * @return string
     */
    public function getName(): string;
}