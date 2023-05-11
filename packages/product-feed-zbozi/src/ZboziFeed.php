<?php

namespace Shopsys\ProductFeed\ZboziBundle;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFacade;

class ZboziFeed implements FeedInterface
{
    protected ZboziFeedInfo $feedInfo;

    protected ZboziFeedItemFacade $feedItemFacade;

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\ZboziFeedInfo $feedInfo
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFacade $feedItemFacade
     */
    public function __construct(ZboziFeedInfo $feedInfo, ZboziFeedItemFacade $feedItemFacade)
    {
        $this->feedInfo = $feedInfo;
        $this->feedItemFacade = $feedItemFacade;
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
