<?php

declare(strict_types=1);

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

    public function refreshProductsVisibilityForMarkedDelayed(): void
    {
        $this->recalcVisibilityForMarked = true;
    }

    public function refreshProductsVisibility(): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility();
    }

    public function refreshProductsVisibilityForMarked(): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility(true);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function markProductsForRecalculationAffectedByCategory(Category $category): void
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
