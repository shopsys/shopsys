<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItemFacade;

class MergadoFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ProductFeed\MergadoBundle\MergadoFeedInfo $mergadoFeedInfo
     * @param \Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItemFacade $mergadoFeedItemFacade
     */
    public function __construct(
        protected MergadoFeedInfo $mergadoFeedInfo,
        protected MergadoFeedItemFacade $mergadoFeedItemFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface
     */
    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->mergadoFeedInfo;
    }

    /**
     * @return string
     */
    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysProductFeedMergado/feed.xml.twig';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable
     */
    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        return $this->mergadoFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
