<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData as BaseProductFilterData;

/**
 * @property \App\Model\Product\Filter\ParameterFilterData[] $parameters
 * @property \App\Model\Product\Flag\Flag[] $flags
 * @property \App\Model\Product\Brand\Brand[] $brands
 */
class ProductFilterData extends BaseProductFilterData
{
    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return bool
     */
    public function isFilterActive(?ReadyCategorySeoMix $readyCategorySeoMix): bool
    {
        if ($readyCategorySeoMix !== null) {
            return false;
        }

        foreach ($this->parameters as $parameterFilterData) {
            if (count($parameterFilterData->values) > 0) {
                return true;
            }
        }

        return !(
            count($this->flags) === 0
            && count($this->brands) === 0
            && ($this->inStock === null || $this->inStock === false)
            && $this->minimalPrice === null
            && $this->maximalPrice === null
        );
    }
}
