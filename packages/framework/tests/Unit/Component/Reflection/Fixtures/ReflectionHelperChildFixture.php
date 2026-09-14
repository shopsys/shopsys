<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures;

use Override;

class ReflectionHelperChildFixture extends ReflectionHelperParentFixture
{
    #[Override]
    public function run(): void
    {
    }

    #[Override, ReflectionHelperMarker('child')]
    public function stop(): void
    {
    }

    #[ReflectionHelperSpecialMarker('special')]
    public function special(): void
    {
    }
}
