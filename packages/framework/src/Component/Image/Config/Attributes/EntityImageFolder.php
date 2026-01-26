<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EntityImageFolder
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
