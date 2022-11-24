<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

class PerformanceTestSample
{
    /**
     * @var string
     */
    private string $routeName;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var float
     */
    private float $duration;

    /**
     * @var int
     */
    private int $queryCount;

    /**
     * @var int
     */
    private int $statusCode;

    /**
     * @var bool
     */
    private bool $isSuccessful;

    /**
     * @param string $routeName
     * @param string $url
     * @param float $duration
     * @param int $queryCount
     * @param int $statusCode
     * @param bool $isSuccessful
     */
    public function __construct(
        $routeName,
        $url,
        $duration,
        $queryCount,
        $statusCode,
        $isSuccessful
    ) {
        $this->routeName = $routeName;
        $this->url = $url;
        $this->duration = $duration;
        $this->queryCount = $queryCount;
        $this->statusCode = $statusCode;
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
}
