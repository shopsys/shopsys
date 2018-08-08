<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CategoryVisibilityRecalculationScheduler
{
    /**
     * @var bool
     */
    private $recalculate = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    private $productVisibilityFacade;

    public function __construct(ProductVisibilityFacade $productVisibilityFacade)
    {
        $this->productVisibilityFacade = $productVisibilityFacade;
    }

    public function scheduleRecalculation(Category $category)
    {
        $this->recalculate = true;
        $this->productVisibilityFacade->markProductsForRecalculationAffectedByCategory($category);
    }

    public function scheduleRecalculationWithoutDependencies()
    {
        $this->recalculate = true;
    }

    public function isRecalculationScheduled()
    {
        return $this->recalculate;
    }
}
