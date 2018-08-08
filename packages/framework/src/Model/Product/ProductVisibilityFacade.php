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

    public function markProductsForRecalculationAffectedByCategory(Category $category)
    {
        $this->productVisibilityRepository->markProductsForRecalculationAffectedByCategory($category);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($this->recalcVisibilityForMarked) {
            $this->refreshProductsVisibilityForMarked();
        }
    }
}
