<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Phpstan\Fixtures;

#[DispatchMarker]
final class DispatchInvokableFixture
{
    public function __invoke(): void
    {
    }

    public function helper(): void
    {
    }
}
