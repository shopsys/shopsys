<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\PersooBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\PersooBundle\Model\FeedItem\PersooFeedItemFacade;

class PersooFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\PersooBundle\PersooFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\PersooBundle\Model\FeedItem\PersooFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly PersooFeedInfo $feedInfo,
        protected readonly PersooFeedItemFacade $feedItemFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(): FeedInfoInterface
    {
        return $this->feedInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateFilepath(): string
    {
        return '@ShopsysProductFeedPersoo/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
