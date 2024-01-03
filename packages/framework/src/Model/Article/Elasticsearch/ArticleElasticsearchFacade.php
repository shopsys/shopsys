<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

class ArticleElasticsearchFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchRepository $articleElasticsearchRepository
     */
    public function __construct(
        protected readonly ArticleElasticsearchRepository $articleElasticsearchRepository,
    ) {
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
     * @param string[] $placements
     * @return array
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
     * @return array
     */
    public function getById(int $articleId): array
    {
        return $this->articleElasticsearchRepository->getById($articleId);
    }
}
