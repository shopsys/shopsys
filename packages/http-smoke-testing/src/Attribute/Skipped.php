<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Skipped
{
}
