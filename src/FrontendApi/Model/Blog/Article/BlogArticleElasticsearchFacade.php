<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Blog\Article;

use App\Model\Blog\Category\BlogCategory;

class BlogArticleElasticsearchFacade
{
    /**
     * @var \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchRepository
     */
    private BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository;

    /**
     * @param \App\FrontendApi\Model\Blog\Article\BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository
     */
    public function __construct(BlogArticleElasticsearchRepository $blogArticleElasticsearchRepository)
    {
        $this->blogArticleElasticsearchRepository = $blogArticleElasticsearchRepository;
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function getByUuid(string $uuid): array
    {
        return $this->blogArticleElasticsearchRepository->getByUuid($uuid);
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getBySlug(string $slug): array
    {
        return $this->blogArticleElasticsearchRepository->getBySlug($slug);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAllBlogArticles(int $offset, int $limit): array
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticles($offset, $limit);
    }

    /**
     * @return int
     */
    public function getAllBlogArticlesTotalCount(): int
    {
        return $this->blogArticleElasticsearchRepository->getAllBlogArticlesTotalCount();
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getByBlogCategory(BlogCategory $blogCategory, int $offset, int $limit): array
    {
        return $this->blogArticleElasticsearchRepository->getByBlogCategory($blogCategory, $offset, $limit);
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @return int
     */
    public function getByBlogCategoryTotalCount(BlogCategory $blogCategory): int
    {
        return $this->blogArticleElasticsearchRepository->getByBlogCategoryTotalCount($blogCategory);
    }
}
