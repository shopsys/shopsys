<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Model\Blog\Category\BlogCategory;

class BlogArticleElasticsearchFacade
{
    /**
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository
     */
    public function __construct(private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository)
    {
    }

    /**
     * @param string $uuid
     * @return mixed[]
     */
    public function getByUuid(string $uuid): array
    {
        return $this->blogArticleElasticsearchRepository->getByUuid($uuid);
    }

    /**
     * @param string $slug
     * @return mixed[]
     */
    public function getBySlug(string $slug): array
    {
        return $this->blogArticleElasticsearchRepository->getBySlug($slug);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param bool $onlyVisibleOnHomepage
     * @return mixed[]
     */
    public function getAllBlogArticles(int $offset, int $limit, bool $onlyVisibleOnHomepage = false): array
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticles($offset, $limit, $onlyVisibleOnHomepage);
    }

    /**
     * @param bool $onlyVisibleOnHomepage
     * @return int
     */
    public function getAllBlogArticlesTotalCount(bool $onlyVisibleOnHomepage = false): int
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticlesTotalCount($onlyVisibleOnHomepage);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $offset
     * @param int $limit
     * @param bool $onlyVisibleOnHomepage
     * @return mixed[]
     */
    public function getByBlogCategory(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage = false,
    ): array {
        return $this->blogArticleElasticsearchRepository->getByBlogCategory($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param bool $onlyVisibleOnHomepage
     * @return int
     */
    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory, bool $onlyVisibleOnHomepage = false): int
    {
        return $this->blogArticleElasticsearchRepository->getByBlogCategoryTotalCount($blogCategory, $onlyVisibleOnHomepage);
    }
}
