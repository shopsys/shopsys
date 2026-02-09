<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFacade;

class ZboziFeed implements FeedInterface
{
    public function __construct(
        protected readonly ZboziFeedInfo $feedInfo,
        protected readonly ZboziFeedItemFacade $feedItemFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->feedInfo;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysProductFeedZbozi/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
