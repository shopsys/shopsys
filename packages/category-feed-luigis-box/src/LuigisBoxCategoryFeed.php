<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle;

use Override;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class LuigisBoxCategoryFeed implements FeedInterface
{
    public function __construct(
        protected readonly LuigisBoxCategoryFeedInfo $luigisBoxCategoryFeedInfo,
        protected readonly LuigisBoxCategoryFeedItemFacade $luigisBoxCategoryFeedItemFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->luigisBoxCategoryFeedInfo;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysCategoryFeedLuigisBox/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->luigisBoxCategoryFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
