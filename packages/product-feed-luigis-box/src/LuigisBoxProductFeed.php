<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItemFacade;

class LuigisBoxProductFeed implements FeedInterface
{
    public function __construct(
        protected readonly LuigisBoxProductFeedInfo $luigisBoxProductFeedInfo,
        protected readonly LuigisBoxProductFeedItemFacade $luigisBoxProductFeedItemFacade,
    ) {
    }

    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->luigisBoxProductFeedInfo;
    }

    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysProductFeedLuigisBox/feed.xml.twig';
    }

    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->luigisBoxProductFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
