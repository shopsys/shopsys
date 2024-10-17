<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;

class ProductFilterDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function create(): ProductFilterData
    {
        return new ProductFilterData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     */
    public function updateProductFilterDataFromReadyCategorySeoMix(
        ReadyCategorySeoMix $readyCategorySeoMix,
        ProductFilterData $productFilterData,
    ): void {
        if ($readyCategorySeoMix->getFlag() !== null) {
            $productFilterData->flags[] = $readyCategorySeoMix->getFlag();
            $productFilterData->flags = array_values($productFilterData->flags);
        }

        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $readyCategorySeoMixParameterParameterValue) {
            $parameterFilterData = new ParameterFilterData();
            $parameterFilterData->parameter = $readyCategorySeoMixParameterParameterValue->getParameter();
            $parameterFilterData->values = [$readyCategorySeoMixParameterParameterValue->getParameterValue()];
            $productFilterData->parameters[] = $parameterFilterData;
        }
    }
}
