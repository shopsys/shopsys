<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CombinedArticle;

class CombinedArticleElasticsearchFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository
     */
    public function __construct(
        protected readonly CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository,
    ) {
    }

    /**
     * @param string $searchText
     * @param int|null $limit
     * @return array
     */
    public function getArticlesBySearchText(string $searchText, ?int $limit = null): array
    {
        return $this->combinedArticleElasticsearchRepository->getArticlesBySearchText($searchText, $limit);
    }
}
