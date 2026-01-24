<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

class PerformanceTestSample
{
    public function __construct(
        private string $routeName,
        private string $url,
        private float $duration,
        private int $queryCount,
        private int $statusCode,
        private bool $isSuccessful,
    ) {
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
