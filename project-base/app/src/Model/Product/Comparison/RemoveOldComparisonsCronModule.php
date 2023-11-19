<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class RemoveOldComparisonsCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Product\Comparison\ComparisonRepository $comparisonRepository
     */
    public function __construct(
        private readonly ComparisonRepository $comparisonRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->comparisonRepository->removeOldComparison(Comparison::DEFAULT_COMPARISON_LIFETIME_DAYS);
    }
}
