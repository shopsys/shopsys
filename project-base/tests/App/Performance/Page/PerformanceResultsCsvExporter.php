<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Tests\App\Performance\JmeterCsvReporter;

class PerformanceResultsCsvExporter
{
    /**
     * @param \Tests\App\Performance\JmeterCsvReporter $jmeterCsvReporter
     */
    public function __construct(private readonly JmeterCsvReporter $jmeterCsvReporter)
    {
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @param string $outputFilename
     */
    public function exportJmeterCsvReport(
        array $performanceTestSamples,
        $outputFilename,
    ) {
        $handle = fopen($outputFilename, 'w');

        $this->jmeterCsvReporter->writeHeader($handle);

        foreach ($performanceTestSamples as $performanceTestSample) {
            $this->jmeterCsvReporter->writeLine(
                $handle,
                $performanceTestSample->getDuration(),
                $performanceTestSample->getRouteName(),
                $performanceTestSample->getStatusCode(),
                $performanceTestSample->isSuccessful(),
                $performanceTestSample->getUrl(),
                $performanceTestSample->getQueryCount(),
            );
        }

        fclose($handle);
    }
}
