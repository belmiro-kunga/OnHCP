<?php

namespace App\Services\Scoring;

use App\Contracts\ScoringStrategyInterface;

class WeightedScoringStrategy implements ScoringStrategyInterface
{
    /**
     * Calculate the score for a simulado attempt using weighted scoring
     * Questions can have different weights based on difficulty or importance
     *
     * @param array $answers User's answers
     * @param array $questions Simulado questions with correct answers and weights
     * @return array Score details including total score, correct answers, etc.
     */
    public function calculateScore(array $answers, array $questions): array
    {
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        $totalWeight = 0;
        $earnedWeight = 0;
        $questionDetails = [];

        foreach ($questions as $question) {
            $questionId = $question['id'];
            $correctAnswer = $question['correct_answer'];
            $weight = $question['weight'] ?? 1; // Default weight is 1
            $userAnswer = $answers[$questionId] ?? null;
            
            $isCorrect = $userAnswer === $correctAnswer;
            if ($isCorrect) {
                $correctAnswers++;
                $earnedWeight += $weight;
            }

            $totalWeight += $weight;

            $questionDetails[] = [
                'question_id' => $questionId,
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect,
                'weight' => $weight,
                'earned_points' => $isCorrect ? $weight : 0,
            ];
        }

        $score = $totalWeight > 0 ? ($earnedWeight / $totalWeight) * 100 : 0;

        return [
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $totalQuestions - $correctAnswers,
            'score' => round($score, 2),
            'total_weight' => $totalWeight,
            'earned_weight' => $earnedWeight,
            'question_details' => $questionDetails,
        ];
    }

    /**
     * Determine if the attempt passed based on the score
     *
     * @param array $scoreDetails Score details from calculateScore
     * @param float $passingScore Minimum score to pass
     * @return bool
     */
    public function hasPassed(array $scoreDetails, float $passingScore): bool
    {
        return $scoreDetails['score'] >= $passingScore;
    }

    /**
     * Get the strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'weighted';
    }
}