<?php

declare(strict_types=1);

namespace App\Component\Image\Kraken;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class KrakenImageOptimizationCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \App\Component\Image\Kraken\KrakenOptimizationFacade
     */
    private $krakenOptimizationFacade;

    /**
     * @param \App\Component\Image\Kraken\KrakenOptimizationFacade $krakenOptimizationFacade
     */
    public function __construct(KrakenOptimizationFacade $krakenOptimizationFacade)
    {
        $this->krakenOptimizationFacade = $krakenOptimizationFacade;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
    }

    public function wakeUp(): void
    {
    }

    /**
     * @return bool
     */
    public function iterate(): bool
    {
        return $this->krakenOptimizationFacade->runOptimization();
    }

    public function sleep(): void
    {
    }
}
