<?php

namespace Shopsys\FrameworkBundle\Component\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarFactory
{
    public function create(OutputInterface $output, int $max): \Symfony\Component\Console\Helper\ProgressBar
    {
        $bar = new ProgressBar($output, $max);
        $this->initializeCustomPlaceholderFormatters();
        $bar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%, Elapsed: %elapsed_hms%, Remaining: %remaining_hms%, MEM:%memory:9s%'
        );
        $bar->setRedrawFrequency(10);
        $bar->start();

        return $bar;
    }

    private function initializeCustomPlaceholderFormatters(): void
    {
        ProgressBar::setPlaceholderFormatterDefinition('remaining_hms', function (ProgressBar $bar) {
            if ($bar->getProgress() !== 0) {
                $secondsPerStep = (time() - $bar->getStartTime()) / $bar->getProgress();
                $remainingSteps = $bar->getMaxSteps() - $bar->getProgress();

                $remainingSeconds = round($secondsPerStep * $remainingSteps);
            } else {
                $remainingSeconds = 0;
            }

            return $this->formatTimeHms($remainingSeconds);
        });

        ProgressBar::setPlaceholderFormatterDefinition('elapsed_hms', function (ProgressBar $bar) {
            return $this->formatTimeHms(time() - $bar->getStartTime());
        });
    }
    
    private function formatTimeHms(int $timeInSeconds): string
    {
        return sprintf(
            '%dh %02dm %02ds',
            floor($timeInSeconds / 3600),
            floor(($timeInSeconds / 60) % 60),
            floor($timeInSeconds % 60)
        );
    }
}
