<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleElasticsearchRepository
{
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ArticleElasticsearchDataFetcher $articleElasticsearchDataFetcher,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function getByUuid(string $uuid): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByUuid($uuid);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            throw new ArticleNotFoundException(sprintf('Article with UUID \'%s\' not found.', $uuid));
        }
    }

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
     * @param string[] $placements
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
     * @param string[] $placements
     */
    public function getAllArticles(int $offset, int $limit, array $placements): array
    {
        $filterQuery = $this->filterQueryFactory->create($offset, $limit);

        if (count($placements) > 0) {
            $filterQuery = $filterQuery->filterByPlacements($placements);
        }

        return $this->articleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    public function getSiteArticleBySlug(string $slug): array
    {
        $article = $this->findSiteArticleBySlug($slug);

        if ($article === null) {
            $article = $this->findSiteArticleBySlug($this->transformStringHelper->addOrRemoveTrailingSlashFromString($slug));
        }

        if ($article === null) {
            throw new ArticleNotFoundException(sprintf('Article with URL slug `%s` does not exist.', $slug));
        }

        return $article;
    }

    protected function findSiteArticleBySlug(string $slug): ?array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug)->filterByType(Article::TYPE_SITE);

        try {
            return $this->articleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            return null;
        }
    }
}
