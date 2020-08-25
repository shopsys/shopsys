<?php

declare(strict_types=1);

namespace App\Model\Blog;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BlogVisibilityRecalculationListener
{
    /**
     * @var \App\Model\Blog\BlogVisibilityRecalculationScheduler
     */
    private $blogVisibilityRecalculationScheduler;

    /**
     * @var \App\Model\Blog\BlogVisibilityFacade
     */
    private $blogVisibilityFacade;

    /**
     * @param \App\Model\Blog\BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler
     * @param \App\Model\Blog\BlogVisibilityFacade $blogVisibilityFacade
     */
    public function __construct(
        BlogVisibilityRecalculationScheduler $blogVisibilityRecalculationScheduler,
        BlogVisibilityFacade $blogVisibilityFacade
    ) {
        $this->blogVisibilityRecalculationScheduler = $blogVisibilityRecalculationScheduler;
        $this->blogVisibilityFacade = $blogVisibilityFacade;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->blogVisibilityRecalculationScheduler->isRecalculationScheduled()) {
            $this->blogVisibilityFacade->refreshBlogCategoriesVisibility();
            $this->blogVisibilityFacade->refreshBlogArticlesVisibility();
        }
    }
}
