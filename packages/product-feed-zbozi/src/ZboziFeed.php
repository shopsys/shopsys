<?php

namespace Shopsys\ProductFeed\ZboziBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFacade;

class ZboziFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\ZboziFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFacade $feedItemFacade
     */
    public function __construct(protected readonly ZboziFeedInfo $feedInfo, protected readonly ZboziFeedItemFacade $feedItemFacade)
    {
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
        return '@ShopsysProductFeedZbozi/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
