<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

class PerformanceTestSampleQualifier
{
    public const STATUS_OK = 0;
    public const STATUS_WARNING = 1;
    public const STATUS_CRITICAL = 2;

    private int $durationWarning;

    private int $durationCritical;

    private int $queryCountWarning;

    private int $queryCountCritical;

    /**
     * @param int $durationWarning
     * @param int $durationCritical
     * @param int $queryCountWarning
     * @param int $queryCountCritical
     */
    public function __construct(int $durationWarning, int $durationCritical, int $queryCountWarning, int $queryCountCritical)
    {
        $this->durationWarning = $durationWarning;
        $this->durationCritical = $durationCritical;
        $this->queryCountWarning = $queryCountWarning;
        $this->queryCountCritical = $queryCountCritical;
    }

    /**
     * @param float $duration
     * @return int
     */
    public function getStatusForDuration($duration): int
    {
        if ($duration >= $this->durationCritical) {
            return self::STATUS_CRITICAL;
        }

        if ($duration >= $this->durationWarning) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

    /**
     * @param int $queryCount
     * @return int
     */
    public function getStatusForQueryCount($queryCount): int
    {
        if ($queryCount >= $this->queryCountCritical) {
            return self::STATUS_CRITICAL;
        }

        if ($queryCount >= $this->queryCountWarning) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample $performanceTestSample
     * @return int
     */
    public function getSampleStatus(PerformanceTestSample $performanceTestSample): int
    {
        $overallStatus = self::STATUS_OK;

        if ($this->getStatusForDuration($performanceTestSample->getDuration()) > $overallStatus) {
            $overallStatus = $this->getStatusForDuration($performanceTestSample->getDuration());
        }

        if ($this->getStatusForQueryCount($performanceTestSample->getQueryCount()) > $overallStatus) {
            $overallStatus = $this->getStatusForQueryCount($performanceTestSample->getQueryCount());
        }

        if (!$performanceTestSample->isSuccessful()) {
            $overallStatus = self::STATUS_CRITICAL;
        }

        return $overallStatus;
    }

    /**
     * @param \Tests\App\Performance\Page\PerformanceTestSample[] $performanceTestSamples
     * @return int
     */
    public function getOverallStatus(array $performanceTestSamples): int
    {
        $allStatuses = [self::STATUS_OK];

        foreach ($performanceTestSamples as $performanceTestSample) {
            $allStatuses[] = $this->getSampleStatus($performanceTestSample);
        }

        return max($allStatuses);
    }
}
