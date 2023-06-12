<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\CategorySeo\ReadyCategorySeoMix;

class ProductFilterDataFactory
{
    /**
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function create(): ProductFilterData
    {
        return new ProductFilterData();
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function createProductFilterDataFromReadyCategorySeoMix(
        ReadyCategorySeoMix $readyCategorySeoMix,
    ): ProductFilterData {
        $productFilterData = $this->create();

        if ($readyCategorySeoMix->getFlag() !== null) {
            $productFilterData->flags = [$readyCategorySeoMix->getFlag()];
        }

        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $readyCategorySeoMixParameterParameterValue) {
            $parameterFilterData = new ParameterFilterData();
            $parameterFilterData->parameter = $readyCategorySeoMixParameterParameterValue->getParameter();
            $parameterFilterData->values = [$readyCategorySeoMixParameterParameterValue->getParameterValue()];
            $productFilterData->parameters[] = $parameterFilterData;
        }

        return $productFilterData;
    }
}
