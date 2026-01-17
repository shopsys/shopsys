<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;

class LuigisBoxArticleFeedItemFacade
{
    public function __construct(
        protected readonly LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory,
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
    ) {
    }

    /**
     * @return iterable<int, \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem>
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        if ($lastSeekId === null) {
            $lastSeekId = 0;
        }
        $articlesData = $this->combinedArticleElasticsearchFacade->getArticlesByDomainId($domainConfig->getId(), $lastSeekId, $maxResults);

        foreach ($articlesData as $article) {
            yield $this->luigisBoxArticleFeedItemFactory->create($article);
        }
    }
}
