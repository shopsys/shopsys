<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class EntityImage
{
    public const string DEFAULT_NAME = 'default';

    public function __construct(
        public readonly string $name = self::DEFAULT_NAME,
        public readonly bool $multiple = false,
    ) {
    }
}
