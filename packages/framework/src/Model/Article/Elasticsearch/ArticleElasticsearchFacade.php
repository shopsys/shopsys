<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

class ArticleElasticsearchFacade
{
    public function __construct(
        protected readonly ArticleElasticsearchRepository $articleElasticsearchRepository,
    ) {
    }

    public function getByUuid(string $uuid): array
    {
        return $this->articleElasticsearchRepository->getByUuid($uuid);
    }

    public function getSiteArticleBySlug(string $slug): array
    {
        return $this->articleElasticsearchRepository->getSiteArticleBySlug($slug);
    }

    /**
     * @param string[] $placements
     */
    public function getAllArticles(int $offset, int $limit, array $placements): array
    {
        return $this->articleElasticsearchRepository->getAllArticles($offset, $limit, $placements);
    }

    /**
     * @param string[] $placements
     */
    public function getAllArticlesTotalCount(array $placements): int
    {
        return $this->articleElasticsearchRepository->getAllArticlesTotalCount($placements);
    }

    public function getById(int $articleId): array
    {
        return $this->articleElasticsearchRepository->getById($articleId);
    }
}
