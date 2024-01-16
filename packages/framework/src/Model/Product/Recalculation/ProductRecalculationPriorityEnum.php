<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

enum ProductRecalculationPriorityEnum: string implements ProductRecalculationPriorityEnumInterface
{
    case HIGH = 'high';
    case REGULAR = 'regular';

    /**
     * @return string
     */
    public static function getPipeSeparatedValues(): string
    {
        return implode('|', array_column(self::cases(), 'value'));
    }
}
