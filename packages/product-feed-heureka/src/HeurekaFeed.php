<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFacade;

class HeurekaFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\HeurekaFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly HeurekaFeedInfo $feedInfo,
        protected readonly HeurekaFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedHeureka/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
