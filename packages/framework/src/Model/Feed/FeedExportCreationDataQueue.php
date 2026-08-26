<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

/**
 * This class holds the data needed to create an instance of FeedExport using FeedFacade::createFeedExport().
 * Usable for easier iterating over all feeds that need to be created.
 *
 * @see \Shopsys\FrameworkBundle\Model\Feed\FeedExport
 * @see \Shopsys\FrameworkBundle\Model\Feed\FeedFacade::createFeedExport()
 */
class FeedExportCreationDataQueue
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModule[] $feedModules
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domains
     */
    public function __construct(
        protected array $feedModules,
        protected array $domains,
    ) {
    }

    public function getCurrentFeedName(): string
    {
        return current($this->feedModules)->getName();
    }

    public function getCurrentDomain(): DomainConfig
    {
        return $this->domains[current($this->feedModules)->getDomainId()];
    }

    public function next(): bool
    {
        array_shift($this->feedModules);

        return !$this->isEmpty();
    }

    public function isEmpty(): bool
    {
        return count($this->feedModules) === 0;
    }
}
