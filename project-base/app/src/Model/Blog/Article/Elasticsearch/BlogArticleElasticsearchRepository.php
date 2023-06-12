<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Component\Elasticsearch\NoResultException;
use App\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use App\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Component\String\TransformString;

class BlogArticleElasticsearchRepository
{
    /**
     * @param \App\Model\Blog\Article\Elasticsearch\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher
     */
    public function __construct(
        private FilterQueryFactory $filterQueryFactory,
        private BlogArticleElasticsearchDataFetcher $blogArticleElasticsearchDataFetcher,
    ) {
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
     * @param bool $onlyVisibleOnHomepage
     * @return int
     */
    public function getAllBlogArticlesTotalCount(bool $onlyVisibleOnHomepage = false): int
    {
        $filterQuery = $this->filterQueryFactory
            ->create()
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param bool $onlyVisibleOnHomepage
     * @return array
     */
    public function getAllBlogArticles(int $offset, int $limit, bool $onlyVisibleOnHomepage = false): array
    {
        $filterQuery = $this->filterQueryFactory
            ->create($offset, $limit)
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug): array
    {
        $blogArticle = $this->findBySlug($slug);
        if ($blogArticle === null) {
            $blogArticle = $this->findBySlug(TransformString::addOrRemoveTrailingSlashFromString($slug));
        }

        if ($blogArticle === null) {
            throw new BlogArticleNotFoundException(sprintf('Blog article not found by slug "%s"', $slug));
        }

        return $blogArticle;
    }

    /**
     * @param string $slug
     * @return array|null
     */
    private function findBySlug(string $slug): ?array
    {
        $filterQuery = $this->filterQueryFactory->createFilteredBySlug($slug);

        try {
            return $this->blogArticleElasticsearchDataFetcher->getSingleResult($filterQuery);
        } catch (NoResultException $exception) {
            return null;
        }
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $offset
     * @param int $limit
     * @param bool $onlyVisibleOnHomepage
     * @return array
     */
    public function getByBlogCategory(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage = false,
    ): array {
        $filterQuery = $this->filterQueryFactory
            ->createFilteredByBlogCategory($blogCategory, $offset, $limit)
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getAllResults($filterQuery);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param bool $onlyVisibleOnHomepage
     * @return int
     */
    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory, bool $onlyVisibleOnHomepage = false): int
    {
        $filterQuery = $this->filterQueryFactory
            ->createFilteredByBlogCategory($blogCategory)
            ->onlyVisibleOnHomepage($onlyVisibleOnHomepage);

        return $this->blogArticleElasticsearchDataFetcher->getTotalCount($filterQuery);
    }
}
