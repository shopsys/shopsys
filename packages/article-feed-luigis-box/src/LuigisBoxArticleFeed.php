<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class LuigisBoxArticleFeed implements FeedInterface
{
    /**
     * @param \Shopsys\ArticleFeed\LuigisBoxBundle\LuigisBoxArticleFeedInfo $luigisBoxArticleFeedInfo
     * @param \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFacade $luigisBoxArticleFacade
     */
    public function __construct(
        protected readonly LuigisBoxArticleFeedInfo $luigisBoxArticleFeedInfo,
        protected readonly LuigisBoxArticleFacade $luigisBoxArticleFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(): FeedInfoInterface
    {
        return $this->luigisBoxArticleFeedInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateFilepath(): string
    {
        return '@ShopsysArticleFeedLuigisBox/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->luigisBoxArticleFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
