<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class EntityLogIdentify
{
    public const IS_LOCALIZED = true;

    public function __construct(
        public bool $isLocalized = false,
    ) {
    }
}
