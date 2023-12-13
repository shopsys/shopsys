<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class BlogVisibilityRecalculationListener
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     */
    public function __construct(
        protected readonly BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        protected readonly BlogVisibilityFacade $blogVisibilityFacade,
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
    }
}
