<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle;

use Override;
use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class LuigisBoxBrandFeed implements FeedInterface
{
    public function __construct(
        protected readonly LuigisBoxBrandFeedInfo $luigisBoxBrandFeedInfo,
        protected readonly LuigisBoxBrandFeedItemFacade $luigisBoxBrandFeedItemFacade,
    ) {
    }

    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->luigisBoxBrandFeedInfo;
    }

    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysBrandFeedLuigisBox/feed.xml.twig';
    }

    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->luigisBoxBrandFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
