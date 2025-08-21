<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Contracts\ScoringStrategyInterface;
use App\Services\Scoring\StandardScoringStrategy;
use App\Services\Scoring\WeightedScoringStrategy;

class ScoringStrategyLSPTest extends TestCase
{
    /**
     * Test that all scoring strategies follow Liskov Substitution Principle
     * They should be interchangeable without breaking the application
     */
    public function test_scoring_strategies_follow_lsp()
    {
        $strategies = [
            new StandardScoringStrategy(),
            new WeightedScoringStrategy(),
        ];

        $answers = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
        ];

        $questions = [
            ['id' => 1, 'correct_answer' => 'A', 'weight' => 1],
            ['id' => 2, 'correct_answer' => 'B', 'weight' => 2],
            ['id' => 3, 'correct_answer' => 'D', 'weight' => 1], // Wrong answer
        ];

        foreach ($strategies as $strategy) {
            $this->assertInstanceOf(ScoringStrategyInterface::class, $strategy);
            
            // Test calculateScore method
            $result = $strategy->calculateScore($answers, $questions);
            
            // All strategies must return these required fields
            $this->assertArrayHasKey('total_questions', $result);
            $this->assertArrayHasKey('correct_answers', $result);
            $this->assertArrayHasKey('incorrect_answers', $result);
            $this->assertArrayHasKey('score', $result);
            $this->assertArrayHasKey('question_details', $result);
            
            // Verify data types and ranges
            $this->assertIsInt($result['total_questions']);
            $this->assertIsInt($result['correct_answers']);
            $this->assertIsInt($result['incorrect_answers']);
            $this->assertIsFloat($result['score']);
            $this->assertIsArray($result['question_details']);
            
            // Score should be between 0 and 100
            $this->assertGreaterThanOrEqual(0, $result['score']);
            $this->assertLessThanOrEqual(100, $result['score']);
            
            // Test hasPassed method
            $this->assertIsBool($strategy->hasPassed($result, 70.0));
            
            // Test getName method
            $this->assertIsString($strategy->getName());
            $this->assertNotEmpty($strategy->getName());
        }
    }

    /**
     * Test that strategies produce consistent results for the same input
     */
    public function test_strategies_consistency()
    {
        $strategy1 = new StandardScoringStrategy();
        $strategy2 = new StandardScoringStrategy();
        
        $answers = [1 => 'A', 2 => 'B'];
        $questions = [
            ['id' => 1, 'correct_answer' => 'A'],
            ['id' => 2, 'correct_answer' => 'B'],
        ];
        
        $result1 = $strategy1->calculateScore($answers, $questions);
        $result2 = $strategy2->calculateScore($answers, $questions);
        
        $this->assertEquals($result1['score'], $result2['score']);
        $this->assertEquals($result1['correct_answers'], $result2['correct_answers']);
    }

    /**
     * Test that strategies handle edge cases properly
     */
    public function test_strategies_handle_edge_cases()
    {
        $strategies = [
            new StandardScoringStrategy(),
            new WeightedScoringStrategy(),
        ];

        foreach ($strategies as $strategy) {
            // Empty questions
            $result = $strategy->calculateScore([], []);
            $this->assertEquals(0, $result['score']);
            $this->assertEquals(0, $result['total_questions']);
            
            // No answers provided
            $questions = [['id' => 1, 'correct_answer' => 'A']];
            $result = $strategy->calculateScore([], $questions);
            $this->assertEquals(0, $result['score']);
            $this->assertEquals(0, $result['correct_answers']);
        }
    }
}