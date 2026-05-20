<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoResultException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleStatusEnum;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleElasticsearchRepository
{
    public function __construct(
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function getByUuid(string $uuid): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByUuid($uuid)
            ->excludePublishedWithFutureDate();

        try {
            return $this->blogArticleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by UUID "%s"', $uuid));
        }
    }

    public function getAllBlogArticlesTotalCount(bool $onlyVisibleOnHomepage = false): int
    {
        $filterQuery = $this->filterQueryFactory
            ->create()
            ->filterByStatus(BlogArticleStatusEnum::STATUS_PUBLISHED)
            ->filterByPublishDateUpToNow()
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }

    public function getAllBlogArticles(int $offset, int $limit, bool $onlyVisibleOnHomepage = false): array
    {
        $filterQuery = $this->filterQueryFactory
            ->create($offset, $limit)
            ->filterByStatus(BlogArticleStatusEnum::STATUS_PUBLISHED)
            ->filterByPublishDateUpToNow()
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    public function getBySlug(string $slug): array
    {
        $blogArticle = $this->findBySlug($slug);

        if ($blogArticle === null) {
            $blogArticle = $this->findBySlug($this->transformStringHelper->addOrRemoveTrailingSlashFromString($slug));
        }

        if ($blogArticle === null) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by slug "%s"', $slug));
        }

        return $blogArticle;
    }

    protected function findBySlug(string $slug): ?array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug)
            ->excludePublishedWithFutureDate();

        try {
            return $this->blogArticleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (ElasticsearchNoResultException $exception) {
            return null;
        }
    }

    public function getByBlogCategory(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage = false,
    ): array {
        $filterQuery = $this->filterQueryFactory
            ->createFilteredByBlogCategory($blogCategory, $offset, $limit)
            ->filterByStatus(BlogArticleStatusEnum::STATUS_PUBLISHED)
            ->filterByPublishDateUpToNow()
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory, bool $onlyVisibleOnHomepage = false): int
    {
        $filterQuery = $this->filterQueryFactory
            ->createFilteredByBlogCategory($blogCategory)
            ->filterByStatus(BlogArticleStatusEnum::STATUS_PUBLISHED)
            ->filterByPublishDateUpToNow()
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }
}
