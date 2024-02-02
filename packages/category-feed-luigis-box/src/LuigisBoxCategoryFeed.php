<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle;

use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class LuigisBoxCategoryFeed implements FeedInterface
{
    /**
     * @param \Shopsys\CategoryFeed\LuigisBoxBundle\LuigisBoxFeedInfo $feedInfo
     * @param \Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly LuigisBoxFeedInfo $feedInfo,
        protected readonly LuigisBoxCategoryFeedItemFacade $feedItemFacade,
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
        return '@ShopsysCategoryFeedLuigisBox/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
