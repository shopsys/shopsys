<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFacade;

class GoogleFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\GoogleBundle\GoogleFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly GoogleFeedInfo $feedInfo,
        protected readonly GoogleFeedItemFacade $feedItemFacade,
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
        return '@ShopsysProductFeedGoogle/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
