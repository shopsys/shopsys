<?php

namespace Tests\ShopBundle\Performance\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PerformanceTestSample
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface
     */
    private $feedInfo;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private $domainConfig;

    /**
     * @var string
     */
    private $generationUri;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string[]
     */
    private $failMessages = [];

    public function __construct(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        $generationUri,
        $duration,
        $statusCode
    ) {
        $this->feedInfo = $feedInfo;
        $this->domainConfig = $domainConfig;
        $this->generationUri = $generationUri;
        $this->duration = $duration;
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param string $failMessage
     */
    public function addFailMessage($failMessage)
    {
        $this->failMessages[] = $failMessage;
    }

    public function getFeedName(): string
    {
        return $this->feedInfo->getName();
    }

    public function getDomainConfig(): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
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
