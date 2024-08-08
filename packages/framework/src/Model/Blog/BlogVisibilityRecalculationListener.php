<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class BlogVisibilityRecalculationListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        protected readonly BlogVisibilityFacade $blogVisibilityFacade,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->blogVisibilityRecalculationScheduler->isRecalculationScheduled()) {
            return;
        }

        $this->blogVisibilityFacade->refreshBlogCategoriesVisibility();
        $this->blogVisibilityFacade->refreshBlogArticlesVisibility();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_CATEGORIES_QUERY_KEY_PART);
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BLOG_ARTICLES_QUERY_KEY_PART);
    }
}
