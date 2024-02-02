<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;

class LuigisBoxArticleFacade
{
    /**
     * @param \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade
     */
    public function __construct(
        protected readonly LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory,
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable<int, \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem>
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        if ($lastSeekId === null) {
            $lastSeekId = 0;
        }
        $articlesData = $this->combinedArticleElasticsearchFacade->getArticlesByDomainId($domainConfig->getId(), $lastSeekId, $maxResults);

        foreach ($articlesData as $article) {
            yield $this->luigisBoxArticleFeedItemFactory->create($article, $lastSeekId);
            $lastSeekId++;
        }
    }
}
