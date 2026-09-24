<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures;

#[ReflectionHelperMarker('parent-class')]
class ReflectionHelperParentFixture
{
    #[ReflectionHelperMarker('parent')]
    public function run(): void
    {
    }

    #[ReflectionHelperMarker('parent')]
    public function stop(): void
    {
    }

    public function plain(): void
    {
    }
}
