<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

class ArticleElasticsearchFacade
{
    /**
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchRepository $articleElasticsearchRepository
     */
    public function __construct(private ArticleElasticsearchRepository $articleElasticsearchRepository)
    {
    }

    /**
     * @param string $uuid
     * @return mixed[]
     */
    public function getByUuid(string $uuid): array
    {
        return $this->articleElasticsearchRepository->getByUuid($uuid);
    }

    /**
     * @param string $slug
     * @return mixed[]
     */
    public function getBySlug(string $slug): array
    {
        return $this->articleElasticsearchRepository->getBySlug($slug);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string[] $placements
     * @return mixed[]
     */
    public function getAllArticles(int $offset, int $limit, array $placements): array
    {
        return $this->articleElasticsearchRepository->getAllArticles($offset, $limit, $placements);
    }

    /**
     * @param string[] $placements
     * @return int
     */
    public function getAllArticlesTotalCount(array $placements): int
    {
        return $this->articleElasticsearchRepository->getAllArticlesTotalCount($placements);
    }

    /**
     * @param int $articleId
     * @return mixed[]
     */
    public function getById(int $articleId): array
    {
        return $this->articleElasticsearchRepository->getById($articleId);
    }
}
