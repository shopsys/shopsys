<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductVisibilityFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository
     */
    protected $productVisibilityRepository;

    /**
     * @var bool
     */
    protected $recalcVisibilityForMarked = false;

    public function __construct(ProductVisibilityRepository $productVisibilityRepository)
    {
        $this->productVisibilityRepository = $productVisibilityRepository;
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

    public function markProductsForRecalculationAffectedByCategory(Category $category): void
    {
        $this->productVisibilityRepository->markProductsForRecalculationAffectedByCategory($category);
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->recalcVisibilityForMarked) {
            $this->refreshProductsVisibilityForMarked();
        }
    }
}
