<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryFeedItemFacade;

class HeurekaDeliveryFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\HeurekaDeliveryBundle\HeurekaDeliveryFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly HeurekaDeliveryFeedInfo $feedInfo,
        protected readonly HeurekaDeliveryFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedHeurekaDelivery/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
