<?php

declare(strict_types=1);

namespace Tests\App\Performance\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PerformanceTestSample
{
    private string $generationUri;

    private float $duration;

    private int $statusCode;

    private ?string $message;

    /**
     * @var string[]
     */
    private array $failMessages = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $generationUri
     * @param float $duration
     * @param int $statusCode
     */
    public function __construct(
        private readonly FeedInfoInterface $feedInfo,
        private readonly DomainConfig $domainConfig,
        string $generationUri,
        float $duration,
        int $statusCode,
    ) {
        $this->generationUri = $generationUri;
        $this->duration = $duration;
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string $failMessage
     */
    public function addFailMessage($failMessage): void
    {
        $this->failMessages[] = $failMessage;
    }

    /**
     * @return string
     */
    public function getFeedName(): string
    {
        return $this->feedInfo->getName();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getDomainConfig(): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
    {
        return $this->domainConfig;
    }

    /**
     * @return string
     */
    public function getGenerationUri(): string
    {
        return $this->generationUri;
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
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string|null
     */
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

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return count($this->failMessages) === 0;
    }
}
