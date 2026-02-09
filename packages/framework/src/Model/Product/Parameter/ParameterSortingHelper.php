<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Collator;

class ParameterSortingHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function sortParameterValuesAlphabetically(array $parameterValues, string $locale): array
    {
        $collator = new Collator($locale);

        uasort($parameterValues, static function (ParameterValue $first, ParameterValue $second) use ($collator) {
            return $collator->compare($first->getText(), $second->getText());
        });

        return $parameterValues;
    }
}
