<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Override;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class LuigisBoxArticleFeed implements FeedInterface
{
    public function __construct(
        protected readonly LuigisBoxArticleFeedInfo $luigisBoxArticleFeedInfo,
        protected readonly LuigisBoxArticleFeedItemFacade $luigisBoxArticleFeedItemFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getInfo(): FeedInfoInterface
    {
        return $this->luigisBoxArticleFeedInfo;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTemplateFilepath(): string
    {
        return '@ShopsysArticleFeedLuigisBox/feed.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        yield from $this->luigisBoxArticleFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
