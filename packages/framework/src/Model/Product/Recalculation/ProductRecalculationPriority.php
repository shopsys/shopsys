<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

class ProductRecalculationPriority
{
    public const string HIGH = 'high';
    public const string REGULAR = 'regular';

    /**
     * @return string
     */
    public static function getPipeSeparatedValues(): string
    {
        return implode('|', ReflectionHelper::getAllPublicClassConstants(static::class));
    }
}
