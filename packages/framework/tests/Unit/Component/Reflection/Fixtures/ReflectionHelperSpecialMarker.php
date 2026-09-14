<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ReflectionHelperSpecialMarker extends ReflectionHelperMarker
{
}
