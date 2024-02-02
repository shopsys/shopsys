<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItemFacade;

class LuigisBoxFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\LuigisBoxFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly LuigisBoxFeedInfo $feedInfo,
        protected readonly LuigisBoxFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedLuigisBox/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
