<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFacade;

class HeurekaFeed implements FeedInterface
{
    public function __construct(
        protected readonly HeurekaFeedInfo $feedInfo,
        protected readonly HeurekaFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedHeureka/feed.xml.twig';
    }

    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
