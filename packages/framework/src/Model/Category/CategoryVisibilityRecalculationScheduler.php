<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CategoryVisibilityRecalculationScheduler
{
    protected bool $recalculate = false;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     */
    public function __construct(protected readonly ProductVisibilityFacade $productVisibilityFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function scheduleRecalculation(Category $category): void
    {
        $this->recalculate = true;
        $this->productVisibilityFacade->markProductsForRecalculationAffectedByCategory($category);
    }

    public function scheduleRecalculationWithoutDependencies(): void
    {
        $this->recalculate = true;
    }

    /**
     * @return bool
     */
    public function isRecalculationScheduled(): bool
    {
        return $this->recalculate;
    }
}
