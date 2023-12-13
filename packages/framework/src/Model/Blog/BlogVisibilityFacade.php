<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog;

use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryVisibilityRepository;

class BlogVisibilityFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleVisibilityRepository $blogArticleVisibilityRepository
     */
    public function __construct(
        protected readonly BlogCategoryVisibilityRepository $blogCategoryVisibilityRepository,
        protected readonly BlogArticleVisibilityRepository $blogArticleVisibilityRepository,
    ) {
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
