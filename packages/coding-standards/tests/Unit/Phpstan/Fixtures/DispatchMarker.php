<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Phpstan\Fixtures;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class DispatchMarker
{
}
