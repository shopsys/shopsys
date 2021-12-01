<?php

declare(strict_types=1);

namespace App\Model\Blog;

use App\Model\Blog\Article\BlogArticleVisibilityRepository;
use App\Model\Blog\Category\BlogCategoryVisibilityRepository;

class BlogVisibilityFacade
{
    /**
     * @var \App\Model\Blog\Category\BlogCategoryVisibilityRepository
     */
    private $blogCategoryVisibilityRepository;

    /**
     * @var \App\Model\Blog\Article\BlogArticleVisibilityRepository
     */
    private $blogArticleVisibilityRepository;

    /**
     * @param \App\Model\Blog\Category\BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository
     * @param \App\Model\Blog\Article\BlogArticleVisibilityRepository $blogArticleVisibilityRepository
     */
    public function __construct(
        BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository,
        BlogArticleVisibilityRepository $blogArticleVisibilityRepository
    ) {
        $this->blogCategoryVisibilityRepository = $blogCategoryVisibilityRepository;
        $this->blogArticleVisibilityRepository = $blogArticleVisibilityRepository;
    }

    public function refreshBlogCategoriesVisibility(): void
    {
        $this->blogCategoryVisibilityRepository->refreshCategoriesVisibility();
    }

    public function refreshBlogArticlesVisibility(): void
    {
        $this->blogArticleVisibilityRepository->refreshArticlesVisibility();
    }
}
