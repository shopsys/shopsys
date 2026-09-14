<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class ReflectionHelperMarker
{
    public function __construct(public string $name)
    {
    }
}
