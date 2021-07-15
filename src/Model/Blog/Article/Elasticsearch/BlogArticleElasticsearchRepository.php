<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Component\Elasticsearch\NoResultException;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use App\Model\Blog\Category\BlogCategory;

class BlogArticleElasticsearchRepository
{
    /**
     * @var \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory
     */
    private FilterQueryFactory $filterQueryFactory;

    /**
     * @var \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher
     */
    private BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher;

    /**
     * @param \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher
     */
    public function __construct(
        FilterQueryFactory $filterQueryFactory,
        BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher
    ) {
        $this->filterQueryFactory = $filterQueryFactory;
        $this->blogArticleElasticsearchDataFetcher = $blogArticleElasticsearchDataFetcher;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByUuid($uuid);
        try {
            return $this->blogArticleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by UUID "%s"', $uuid));
        }
    }

    /**
     * @return int
     */
    public function getAllBlogArticlesTotalCount(): int
    {
        $filterQuery = $this->filterQueryFactory->create();

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllBlogArticles(int $offset, int $limit): array
    {
        $filterQuery = $this->filterQueryFactory->create($offset, $limit);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug);

        try {
            return $this->blogArticleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by slug "%s"', $slug));
        }
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByBlogCategory(BlogCategory $blogCategory, int $offset, int $limit): array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByBlogCategory($blogCategory, $offset, $limit);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @return int
     */
    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory): int
    {
        $filterQuery = $this->filterQueryFactory->createFilteredByBlogCategory($blogCategory);

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }
}
