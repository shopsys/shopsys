<?php

declare(strict_types=1);

namespace App\Model\Article;

class CombinedArticleElasticsearchFacade
{
    /**
     * @param \App\Model\Article\CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository
     */
    public function __construct(private CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository)
    {
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
