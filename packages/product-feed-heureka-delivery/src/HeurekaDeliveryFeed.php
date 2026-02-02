<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryFeedItemFacade;

class HeurekaDeliveryFeed implements FeedInterface
{
    public function __construct(
        protected readonly HeurekaDeliveryFeedInfo $feedInfo,
        protected readonly HeurekaDeliveryFeedItemFacade $feedItemFacade,
    ) {
    }

    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->feedInfo;
    }

    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysProductFeedHeurekaDelivery/feed.xml.twig';
    }

    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
