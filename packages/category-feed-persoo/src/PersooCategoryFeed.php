<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle;

use Shopsys\CategoryFeed\PersooBundle\Model\FeedItem\PersooCategoryFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class PersooCategoryFeed implements FeedInterface
{
    /**
     * @param \Shopsys\CategoryFeed\PersooBundle\PersooFeedInfo $feedInfo
     * @param \Shopsys\CategoryFeed\PersooBundle\Model\FeedItem\PersooCategoryFeedItemFacade $feedItemFacade
     */
    public function __construct(
        protected readonly PersooFeedInfo $feedInfo,
        protected readonly PersooCategoryFeedItemFacade $feedItemFacade,
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
        return '@ShopsysCategoryFeedPersoo/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->feedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
