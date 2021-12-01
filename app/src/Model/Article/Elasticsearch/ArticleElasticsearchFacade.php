<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

class ArticleElasticsearchFacade
{
    /**
     * @var \App\Model\Article\Elasticsearch\ArticleElasticsearchRepository
     */
    private ArticleElasticsearchRepository $articleElasticsearchRepository;

    /**
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchRepository $articleElasticsearchRepository
     */
    public function __construct(ArticleElasticsearchRepository $articleElasticsearchRepository)
    {
        $this->articleElasticsearchRepository = $articleElasticsearchRepository;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        return $this->articleElasticsearchRepository->getByUuid($uuid);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug): array
    {
        return $this->articleElasticsearchRepository->getBySlug($slug);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string|null $placement
     * @return array
     */
    public function getAllArticles(int $offset, int $limit, ?string $placement = null): array
    {
        return $this->articleElasticsearchRepository->getAllArticles($offset, $limit, $placement);
    }

    /**
     * @param string|null $placement
     * @return int
     */
    public function getAllArticlesTotalCount(?string $placement = null): int
    {
        return $this->articleElasticsearchRepository->getAllArticlesTotalCount($placement);
    }

    /**
     * @param int $articleId
     * @return array
     */
    public function getById(int $articleId): array
    {
        return $this->articleElasticsearchRepository->getById($articleId);
    }
}
