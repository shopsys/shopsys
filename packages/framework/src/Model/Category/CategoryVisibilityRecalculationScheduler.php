<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CategoryVisibilityRecalculationScheduler
{
    protected bool $recalculate = false;

    public function __construct(protected readonly ProductVisibilityFacade $productVisibilityFacade)
    {
    }

    public function scheduleRecalculation(): void
    {
        $this->recalculate = true;
    }

    public function isRecalculationScheduled(): bool
    {
        return $this->recalculate;
    }
}
