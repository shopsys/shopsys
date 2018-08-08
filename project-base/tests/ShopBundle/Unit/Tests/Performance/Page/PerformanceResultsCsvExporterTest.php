<?php

namespace Tests\ShopBundle\Unit\Tests\Performance\Page;

use PHPUnit\Framework\TestCase;
use Tests\ShopBundle\Performance\JmeterCsvReporter;
use Tests\ShopBundle\Performance\Page\PerformanceResultsCsvExporter;
use Tests\ShopBundle\Performance\Page\PerformanceTestSample;

class PerformanceResultsCsvExporterTest extends TestCase
{
    public function testExportJmeterCsvReportWritesExpectedHeader(): void
    {
        $outputFilename = $this->getTemporaryFilename();

        $performanceResultsCsvExporter = $this->createPerformanceResultsCsvExporter();

        $performanceResultsCsvExporter->exportJmeterCsvReport(
            $this->getPerformanceTestSamples(),
            $outputFilename
        );

        $expectedLine = [
            'timestamp',
            'elapsed',
            'label',
            'responseCode',
            'success',
            'URL',
            'Variables',
        ];

        $this->assertCsvRowEquals($expectedLine, $outputFilename, 0);
    }

    public function testExportJmeterCsvReportRoundsDuration(): void
    {
        $outputFilename = $this->getTemporaryFilename();

        $performanceResultsCsvExporter = $this->createPerformanceResultsCsvExporter();

        $performanceResultsCsvExporter->exportJmeterCsvReport(
            $this->getPerformanceTestSamples(),
            $outputFilename
        );

        $line = $this->getCsvLine($outputFilename, 1);

        $this->assertEquals(1000, $line[1]);
    }

    private function getTemporaryFilename(): string
    {
        return tempnam(sys_get_temp_dir(), 'test');
    }

    /**
     * @return \Tests\ShopBundle\Performance\Page\PerformanceTestSample[]
     */
    private function getPerformanceTestSamples(): array
    {
        $performanceTestSamples = [];

        $performanceTestSamples[] = new PerformanceTestSample(
            'routeName1',
            'url1',
            1000.1,
            10,
            200,
            true
        );
        $performanceTestSamples[] = new PerformanceTestSample(
            'routeName2',
            'url2',
            2000,
            20,
            301,
            true
        );

        return $performanceTestSamples;
    }
    
    private function assertCsvRowEquals(array $expectedLine, string $filename, int $lineIndex): void
    {
        $actualLine = $this->getCsvLine($filename, $lineIndex);

        $this->assertSame($expectedLine, $actualLine);
    }
    
    private function getCsvLine(string $filename, int $lineIndex): array
    {
        $handle = fopen($filename, 'r');

        // seek to $rowIndex
        for ($i = 0; $i < $lineIndex; $i++) {
            fgetcsv($handle);
        }

        return fgetcsv($handle);
    }

    private function createPerformanceResultsCsvExporter(): \Tests\ShopBundle\Performance\Page\PerformanceResultsCsvExporter
    {
        return new PerformanceResultsCsvExporter(new JmeterCsvReporter());
    }
}
