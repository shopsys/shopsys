<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductRecalculationPriorityEnum extends AbstractEnum
{
    public const string HIGH = 'high';
    public const string REGULAR = 'regular';

    /**
     * @return string
     */
    public function getPipeSeparatedValues(): string
    {
        return implode('|', $this->getAllCases());
    }
}
