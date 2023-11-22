<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

class PerformanceTestSamplesAggregator
{
    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @return \Tests\App\Performance\Page\PerformanceTestSample[]
     */
    public function getPerformanceTestSamplesAggregatedByUrl(
        array $performanceTestSamples,
    ): array {
        $aggregatedPerformanceTestSamples = [];

        $performanceTestSamplesGroupedByUrl = $this->getPerformanceTestSamplesGroupedByUrl($performanceTestSamples);

        foreach ($performanceTestSamplesGroupedByUrl as $url => $performanceTestSamplesOfUrl) {
            $samplesCount = 0;
            $totalDuration = 0;
            $maxQueryCount = 0;
            $isSuccessful = true;
            $worstStatusCode = null;
            $performanceTestSample = null;

            /** @var \Tests\App\Performance\Page\PerformanceTestSample $performanceTestSample */
            foreach ($performanceTestSamplesOfUrl as $performanceTestSample) {
                $samplesCount++;
                $totalDuration += $performanceTestSample->getDuration();

                if ($performanceTestSample->getQueryCount() > $maxQueryCount) {
                    $maxQueryCount = $performanceTestSample->getQueryCount();
                }

                if (!$performanceTestSample->isSuccessful()) {
                    $isSuccessful = false;
                }

                if ($performanceTestSample->isSuccessful() || $worstStatusCode === null) {
                    $worstStatusCode = $performanceTestSample->getStatusCode();
                }
            }

            $aggregatedPerformanceTestSamples[$url] = new PerformanceTestSample(
                $performanceTestSample->getRouteName(),
                $url,
                $totalDuration / $samplesCount,
                $maxQueryCount,
                $worstStatusCode,
                $isSuccessful,
            );
        }

        return $aggregatedPerformanceTestSamples;
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @return \non-empty-array<\int<0, \max>, \mixed>[]
     */
    private function getPerformanceTestSamplesGroupedByUrl(array $performanceTestSamples): array
    {
        $performanceTestSamplesGroupedByUrl = [];

        foreach ($performanceTestSamples as $performanceTestSample) {
            $performanceTestSamplesGroupedByUrl[$performanceTestSample->getUrl()][] = $performanceTestSample;
        }

        return $performanceTestSamplesGroupedByUrl;
    }
}
