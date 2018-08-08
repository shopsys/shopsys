<?php

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
     * @var array
     */
    private $dataInQueue = [];

    /**
     * @param string[] $feedNames
     * @param DomainConfig[] $domains
     */
    public function __construct(array $feedNames, array $domains)
    {
        foreach ($feedNames as $feedName) {
            foreach ($domains as $domain) {
                $this->dataInQueue[] = ['feed_name' => $feedName, 'domain' => $domain];
            }
        }
    }

    public function getCurrentFeedName(): string
    {
        return current($this->dataInQueue)['feed_name'];
    }

    public function getCurrentDomain(): DomainConfig
    {
        return current($this->dataInQueue)['domain'];
    }

    public function next(): bool
    {
        array_shift($this->dataInQueue);

        return !$this->isEmpty();
    }

    public function isEmpty(): bool
    {
        return count($this->dataInQueue) === 0;
    }
}
