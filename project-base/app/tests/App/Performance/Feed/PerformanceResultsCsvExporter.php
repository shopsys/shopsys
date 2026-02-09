<?php

declare(strict_types=1);

namespace Tests\App\Performance\Feed;

use Tests\App\Performance\JmeterCsvReporter;

class PerformanceResultsCsvExporter
{
    public function __construct(private readonly JmeterCsvReporter $jmeterCsvReporter)
    {
    }

    /**
     * @param \Tests\App\Performance\Feed\PerformanceTestSample[] $performanceTestSamples
     */
    public function exportJmeterCsvReport(
        array $performanceTestSamples,
        string $outputFilename,
    ): void {
        $handle = fopen($outputFilename, 'w');

        $this->jmeterCsvReporter->writeHeader($handle);

        foreach ($performanceTestSamples as $performanceTestSample) {
            $this->jmeterCsvReporter->writeLine(
                $handle,
                $performanceTestSample->getDuration() * 1000,
                AllFeedsTest::ROUTE_NAME_GENERATE_FEED,
                $performanceTestSample->getStatusCode(),
                $performanceTestSample->isSuccessful(),
                $performanceTestSample->getGenerationUri(),
                0, // Currently we are not able to measure query count
            );
        }

        fclose($handle);
    }
}
