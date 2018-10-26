<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Helper;

final class PhpdocRegex
{
    /**
     * "@param typ $value"
     * ↓
     * "$value"
     *
     * @var string
     */
    public const ARGUMENT_NAME_PATTERN = '#^[^$]+(\$\w+).*$#s';
}
