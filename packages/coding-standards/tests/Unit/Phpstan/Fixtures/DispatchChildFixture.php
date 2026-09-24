<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Phpstan\Fixtures;

use Override;

final class DispatchChildFixture extends DispatchParentFixture
{
    #[Override]
    public function handle(): void
    {
    }
}
