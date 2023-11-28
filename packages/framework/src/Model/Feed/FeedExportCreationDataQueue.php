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

    /**
     * @return string
     */
    public function getCurrentFeedName(): string
    {
        return current($this->feedModules)->getName();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getCurrentDomain(): DomainConfig
    {
        return $this->domains[current($this->feedModules)->getDomainId()];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule
     */
    public function getCurrentFeedModule(): FeedModule
    {
        return current($this->feedModules);
    }

    /**
     * @return bool
     */
    public function next(): bool
    {
        array_shift($this->feedModules);

        return !$this->isEmpty();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->feedModules) === 0;
    }
}
