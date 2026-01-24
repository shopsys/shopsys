<?php

declare(strict_types=1);

namespace Tests\App\Performance\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PerformanceTestSample
{
    private ?string $message = null;

    /**
     * @var string[]
     */
    private array $failMessages = [];

    public function __construct(
        private readonly FeedInfoInterface $feedInfo,
        private readonly DomainConfig $domainConfig,
        private string $generationUri,
        private float $duration,
        private int $statusCode,
    ) {
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function addFailMessage(string $failMessage): void
    {
        $this->failMessages[] = $failMessage;
    }

    public function getFeedName(): string
    {
        return $this->feedInfo->getName();
    }

    public function getDomainConfig(): DomainConfig
    {
        return $this->domainConfig;
    }

    public function getGenerationUri(): string
    {
        return $this->generationUri;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string[]
     */
    public function getFailMessages(): array
    {
        return $this->failMessages;
    }

    public function isSuccessful(): bool
    {
        return count($this->failMessages) === 0;
    }
}
