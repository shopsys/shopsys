<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\PersooBundle;

use Shopsys\ArticleFeed\PersooBundle\Model\PersooArticleFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class PersooArticleFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ArticleFeed\PersooBundle\PersooArticleFeedInfo $persooArticleFeedInfo
     * @param \Shopsys\ArticleFeed\PersooBundle\Model\PersooArticleFacade $persooArticleFacade
     */
    public function __construct(
        protected readonly PersooArticleFeedInfo $persooArticleFeedInfo,
        protected readonly PersooArticleFacade $persooArticleFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(): FeedInfoInterface
    {
        return $this->persooArticleFeedInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateFilepath(): string
    {
        return '@ShopsysArticleFeedPersoo/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->persooArticleFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
