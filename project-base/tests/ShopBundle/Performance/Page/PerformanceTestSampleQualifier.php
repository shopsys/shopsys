<?php

namespace Tests\ShopBundle\Performance\Page;

class PerformanceTestSampleQualifier
{
    const STATUS_OK = 0;
    const STATUS_WARNING = 1;
    const STATUS_CRITICAL = 2;

    /**
     * @var int
     */
    private $durationWarning;

    /**
     * @var int
     */
    private $durationCritical;

    /**
     * @var int
     */
    private $queryCountWarning;

    /**
     * @var int
     */
    private $queryCountCritical;

    public function __construct(int $durationWarning, int $durationCritical, int $queryCountWarning, int $queryCountCritical)
    {
        $this->durationWarning = $durationWarning;
        $this->durationCritical = $durationCritical;
        $this->queryCountWarning = $queryCountWarning;
        $this->queryCountCritical = $queryCountCritical;
    }

    public function getStatusForDuration(float $duration): int
    {
        if ($duration >= $this->durationCritical) {
            return self::STATUS_CRITICAL;
        } elseif ($duration >= $this->durationWarning) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

    public function getStatusForQueryCount(int $queryCount): int
    {
        if ($queryCount >= $this->queryCountCritical) {
            return self::STATUS_CRITICAL;
        } elseif ($queryCount >= $this->queryCountWarning) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

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
     * @param \Tests\ShopBundle\Performance\Page\PerformanceTestSample[] $performanceTestSamples
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
