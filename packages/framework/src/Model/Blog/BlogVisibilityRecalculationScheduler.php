<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog;

class BlogVisibilityRecalculationScheduler
{
    protected bool $recalculate = false;

    public function scheduleRecalculation(): void
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
