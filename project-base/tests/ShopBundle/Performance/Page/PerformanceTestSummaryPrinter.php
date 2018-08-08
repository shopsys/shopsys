<?php

namespace Tests\ShopBundle\Performance\Page;

use Symfony\Component\Console\Output\ConsoleOutput;

class PerformanceTestSummaryPrinter
{
    /**
     * @var \Tests\ShopBundle\Performance\Page\PerformanceTestSampleQualifier
     */
    private $performanceTestSampleQualifier;

    public function __construct(PerformanceTestSampleQualifier $performanceTestSampleQualifier)
    {
        $this->performanceTestSampleQualifier = $performanceTestSampleQualifier;
    }

    /**
     * @param \Tests\ShopBundle\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     */
    public function printSummary(
        array $performanceTestSamples,
        ConsoleOutput $consoleOutput
    ): void {
        foreach ($performanceTestSamples as $performanceTestSample) {
            $sampleStatus = $this->performanceTestSampleQualifier->getSampleStatus($performanceTestSample);

            if ($sampleStatus !== PerformanceTestSampleQualifier::STATUS_OK) {
                $this->printSample($performanceTestSample, $consoleOutput);
            }
        }

        $resultStatus = $this->performanceTestSampleQualifier->getOverallStatus($performanceTestSamples);
        $resultColor = $this->getStatusConsoleTextColor($resultStatus);
        $resultTag = 'fg=' . $resultColor;
        $consoleOutput->writeln('');
        switch ($resultStatus) {
            case PerformanceTestSampleQualifier::STATUS_OK:
                $consoleOutput->write('<' . $resultTag . '>Test passed</' . $resultTag . '>');
                return;
            case PerformanceTestSampleQualifier::STATUS_WARNING:
                $consoleOutput->write('<' . $resultTag . '>Test passed, but contains some warnings</' . $resultTag . '>');
                return;
            case PerformanceTestSampleQualifier::STATUS_CRITICAL:
            default:
                $consoleOutput->write('<' . $resultTag . '>Test failed</' . $resultTag . '>');
                return;
        }
    }

    private function printSample(
        PerformanceTestSample $performanceTestSample,
        ConsoleOutput $consoleOutput
    ): void {
        $consoleOutput->writeln('');
        $consoleOutput->writeln(
            'Route name: ' . $performanceTestSample->getRouteName() . ' (' . $performanceTestSample->getUrl() . ')'
        );

        $tag = $this->getFormatterTagForDuration($performanceTestSample->getDuration());
        $consoleOutput->writeln(
            '<' . $tag . '>Average duration: ' . round($performanceTestSample->getDuration()) . 'ms</' . $tag . '>'
        );

        $tag = $this->getFormatterTagForQueryCount($performanceTestSample->getQueryCount());
        $consoleOutput->writeln(
            '<' . $tag . '>Max query count: ' . $performanceTestSample->getQueryCount() . '</' . $tag . '>'
        );

        if (!$performanceTestSample->isSuccessful()) {
            $tag = $this->getFormatterTagForError();
            $consoleOutput->writeln('<' . $tag . '>Wrong response status code</' . $tag . '>');
        }
    }
    
    private function getFormatterTagForDuration(float $duration): string
    {
        $status = $this->performanceTestSampleQualifier->getStatusForDuration($duration);
        return 'fg=' . $this->getStatusConsoleTextColor($status);
    }
    
    private function getFormatterTagForQueryCount(int $queryCount): string
    {
        $status = $this->performanceTestSampleQualifier->getStatusForQueryCount($queryCount);
        return 'fg=' . $this->getStatusConsoleTextColor($status);
    }

    private function getFormatterTagForError(): string
    {
        return 'fg=' . $this->getStatusConsoleTextColor(PerformanceTestSampleQualifier::STATUS_CRITICAL);
    }
    
    private function getStatusConsoleTextColor(int $status): string
    {
        switch ($status) {
            case PerformanceTestSampleQualifier::STATUS_OK:
                return 'green';
            case PerformanceTestSampleQualifier::STATUS_WARNING:
                return 'yellow';
            default:
                return 'red';
        }
    }
}
