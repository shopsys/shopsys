<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

use App\Component\Elasticsearch\NoResultException;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleElasticsearchRepository
{
    /**
     * @param \App\Model\Article\Elasticsearch\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchDataFetcher $articleElasticsearchDataFetcher
     */
    public function __construct(
        private FilterQueryFactory $filterQueryFactory,
        private ArticleElasticsearchDataFetcher $articleElasticsearchDataFetcher,
    ) {
    }

    /**
     * @param string $uuid
     * @return mixed[]
     */
    public function getByUuid(string $uuid): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByUuid($uuid);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            throw new ArticleNotFoundException(sprintf('Article with UUID \'%s\' not found.', $uuid));
        }
    }

    /**
     * @param int $articleId
     * @return mixed[]
     */
    public function getById(int $articleId): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredById($articleId);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            throw new ArticleNotFoundException(sprintf('Article not found by id "%s"', $articleId));
        }
    }

    /**
     * @param string[] $placements
     * @return int
     */
    public function getAllArticlesTotalCount(array $placements): int
    {
        $filterQuery = $this->filterQueryFactory->create();

        if (count($placements) > 0) {
            $filterQuery = $filterQuery->filterByPlacements($placements);
        }

        return $this->articleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param string[] $placements
     * @return mixed[]
     */
    public function getAllArticles(int $offset, int $limit, array $placements): array
    {
        $filterQuery = $this->filterQueryFactory->create($offset, $limit);

        if (count($placements) > 0) {
            $filterQuery = $filterQuery->filterByPlacements($placements);
        }

        return $this->articleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    /**
     * @param string $slug
     * @return mixed[]
     */
    public function getBySlug(string $slug): array
    {
        $article = $this->findBySlug($slug);

        if ($article === null) {
            $article = $this->findBySlug(TransformString::addOrRemoveTrailingSlashFromString($slug));
        }

        if ($article === null) {
            throw new ArticleNotFoundException(sprintf('Article with URL slug `%s` does not exist.', $slug));
        }

        return $article;
    }

    /**
     * @param string $slug
     * @return array|null
     */
    private function findBySlug(string $slug): ?array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            return null;
        }
    }
}
