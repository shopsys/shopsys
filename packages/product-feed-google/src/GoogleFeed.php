<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFacade;

class GoogleFeed implements FeedInterface
{
    public function __construct(
        protected readonly GoogleFeedInfo $feedInfo,
        protected readonly GoogleFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedGoogle/feed.xml.twig';
    }

    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
