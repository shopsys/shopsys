<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleElasticsearchFacade
{
    public function __construct(
        protected readonly BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository,
    ) {
    }

    public function getByUuid(string $uuid): array
    {
        return $this->blogArticleElasticsearchRepository->getByUuid($uuid);
    }

    public function getBySlug(string $slug): array
    {
        return $this->blogArticleElasticsearchRepository->getBySlug($slug);
    }

    public function getAllBlogArticles(int $offset, int $limit, bool $onlyVisibleOnHomepage = false): array
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticles($offset, $limit, $onlyVisibleOnHomepage);
    }

    public function getAllBlogArticlesTotalCount(bool $onlyVisibleOnHomepage = false): int
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticlesTotalCount($onlyVisibleOnHomepage);
    }

    public function getByBlogCategory(
        BlogCategory $blogCategory,
        int $offset,
        int $limit,
        bool $onlyVisibleOnHomepage = false,
    ): array {
        return $this->blogArticleElasticsearchRepository->getByBlogCategory($blogCategory, $offset, $limit, $onlyVisibleOnHomepage);
    }

    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory, bool $onlyVisibleOnHomepage = false): int
    {
        return $this->blogArticleElasticsearchRepository->getByBlogCategoryTotalCount($blogCategory, $onlyVisibleOnHomepage);
    }
}
