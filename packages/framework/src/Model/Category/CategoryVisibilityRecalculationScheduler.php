<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

class CategoryVisibilityRecalculationScheduler
{
    protected bool $recalculate = false;

    public function scheduleRecalculation(): void
    {
        $this->recalculate = true;
    }

    public function isRecalculationScheduled(): bool
    {
        return $this->recalculate;
    }
}
