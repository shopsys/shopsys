<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ProductVisibilityFacade
{
    protected bool $recalcVisibilityForMarked = false;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     */
    public function __construct(protected readonly ProductVisibilityRepository $productVisibilityRepository)
    {
    }

    public function refreshProductsVisibilityForMarkedDelayed()
    {
        $this->recalcVisibilityForMarked = true;
    }

    public function refreshProductsVisibility()
    {
        $this->productVisibilityRepository->refreshProductsVisibility();
    }

    public function refreshProductsVisibilityForMarked()
    {
        $this->productVisibilityRepository->refreshProductsVisibility(true);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function markProductsForRecalculationAffectedByCategory(Category $category)
    {
        $this->productVisibilityRepository->markProductsForRecalculationAffectedByCategory($category);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->recalcVisibilityForMarked) {
            $this->refreshProductsVisibilityForMarked();
        }
    }
}
