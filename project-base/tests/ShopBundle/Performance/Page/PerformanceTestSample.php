<?php

namespace Tests\ShopBundle\Performance\Page;

class PerformanceTestSample
{
    /**
     * @var string
     */
    private $routeName;

    /**
     * @var string
     */
    private $url;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var int
     */
    private $queryCount;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var bool
     */
    private $isSuccessful;

    public function __construct(
        string $routeName,
        string $url,
        float $duration,
        int $queryCount,
        int $statusCode,
        bool $isSuccessful
    ) {
        $this->routeName = $routeName;
        $this->url = $url;
        $this->duration = $duration;
        $this->queryCount = $queryCount;
        $this->statusCode = $statusCode;
        $this->isSuccessful = $isSuccessful;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }
}
