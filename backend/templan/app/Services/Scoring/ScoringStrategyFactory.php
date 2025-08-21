<?php

namespace App\Services\Scoring;

use App\Contracts\ScoringStrategyInterface;
use InvalidArgumentException;

class ScoringStrategyFactory
{
    /**
     * Available scoring strategies
     *
     * @var array
     */
    private static array $strategies = [
        'standard' => StandardScoringStrategy::class,
        'weighted' => WeightedScoringStrategy::class,
    ];

    /**
     * Create a scoring strategy instance
     *
     * @param string $strategyName
     * @return ScoringStrategyInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $strategyName): ScoringStrategyInterface
    {
        if (!isset(self::$strategies[$strategyName])) {
            throw new InvalidArgumentException("Scoring strategy '{$strategyName}' not found.");
        }

        $strategyClass = self::$strategies[$strategyName];
        return new $strategyClass();
    }

    /**
     * Register a new scoring strategy
     *
     * @param string $name
     * @param string $strategyClass
     * @return void
     * @throws InvalidArgumentException
     */
    public static function register(string $name, string $strategyClass): void
    {
        if (!class_exists($strategyClass)) {
            throw new InvalidArgumentException("Strategy class '{$strategyClass}' does not exist.");
        }

        if (!in_array(ScoringStrategyInterface::class, class_implements($strategyClass))) {
            throw new InvalidArgumentException("Strategy class '{$strategyClass}' must implement ScoringStrategyInterface.");
        }

        self::$strategies[$name] = $strategyClass;
    }

    /**
     * Get all available strategy names
     *
     * @return array
     */
    public static function getAvailableStrategies(): array
    {
        return array_keys(self::$strategies);
    }

    /**
     * Check if a strategy exists
     *
     * @param string $strategyName
     * @return bool
     */
    public static function exists(string $strategyName): bool
    {
        return isset(self::$strategies[$strategyName]);
    }
}