<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleElasticsearchRepository
{
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ArticleElasticsearchDataFetcher $articleElasticsearchDataFetcher,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getByUuid(string $uuid): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByUuid($uuid);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            throw new ArticleNotFoundException(sprintf('Article with UUID \'%s\' not found.', $uuid));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getById(int $articleId): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredById($articleId);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            throw new ArticleNotFoundException(sprintf('Article not found by id "%s"', $articleId));
        }
    }

    /**
     * @param array<int, string> $placements
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
     * @param array<int, string> $placements
     * @return array<int, array<string, mixed>>
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
     * @return array<string, mixed>
     */
    public function getBySlug(string $slug): array
    {
        $article = $this->findBySlug($slug);

        if ($article === null) {
            $article = $this->findBySlug($this->transformStringHelper->addOrRemoveTrailingSlashFromString($slug));
        }

        if ($article === null) {
            throw new ArticleNotFoundException(sprintf('Article with URL slug `%s` does not exist.', $slug));
        }

        return $article;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findBySlug(string $slug): ?array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            return null;
        }
    }
}
