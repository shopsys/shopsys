<?php

declare(strict_types=1);

namespace App\Model\Article;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class CombinedArticleElasticsearchFacade
{
    /**
     * @var \App\Model\Article\CombinedArticleElasticsearchRepository
     */
    private CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository;

    /**
     * @param \App\Model\Article\CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository
     */
    public function __construct(CombinedArticleElasticsearchRepository $combinedArticleElasticsearchRepository)
    {
        $this->combinedArticleElasticsearchRepository = $combinedArticleElasticsearchRepository;
    }

    /**
     * @param string $searchText
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteArticles(string $searchText, int $limit): PaginationResult
    {
        return $this->combinedArticleElasticsearchRepository->getSearchAutocompleteArticles($searchText, $limit);
    }
}
