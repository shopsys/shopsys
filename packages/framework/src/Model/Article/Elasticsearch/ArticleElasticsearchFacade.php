<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

class ArticleElasticsearchFacade
{
    public function __construct(
        protected readonly ArticleElasticsearchRepository $articleElasticsearchRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getByUuid(string $uuid): array
    {
        return $this->articleElasticsearchRepository->getByUuid($uuid);
    }

    /**
     * @return array<string, mixed>
     */
    public function getBySlug(string $slug): array
    {
        return $this->articleElasticsearchRepository->getBySlug($slug);
    }

    /**
     * @param array<int, string> $placements
     * @return array<int, array<string, mixed>>
     */
    public function getAllArticles(int $offset, int $limit, array $placements): array
    {
        return $this->articleElasticsearchRepository->getAllArticles($offset, $limit, $placements);
    }

    /**
     * @param array<int, string> $placements
     */
    public function getAllArticlesTotalCount(array $placements): int
    {
        return $this->articleElasticsearchRepository->getAllArticlesTotalCount($placements);
    }

    /**
     * @return array<string, mixed>
     */
    public function getById(int $articleId): array
    {
        return $this->articleElasticsearchRepository->getById($articleId);
    }
}
