<?php

declare(strict_types=1);

namespace App\Model\Blog;

class BlogVisibilityRecalculationScheduler
{
    private bool $recalculate = false;

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
