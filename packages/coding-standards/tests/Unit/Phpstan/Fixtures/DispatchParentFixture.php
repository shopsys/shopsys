<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Phpstan\Fixtures;

class DispatchParentFixture
{
    #[DispatchMarker]
    public function handle(): void
    {
    }

    public function helper(): void
    {
    }
}
