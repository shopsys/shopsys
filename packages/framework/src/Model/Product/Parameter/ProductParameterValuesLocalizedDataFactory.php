<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValuesLocalizedDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData
     */
    protected function createInstance(): ProductParameterValuesLocalizedData
    {
        return new ProductParameterValuesLocalizedData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData
     */
    public function createFromProductParameterValueData(
        ProductParameterValueData $productParameterValueData,
    ): ProductParameterValuesLocalizedData {
        $productParameterValuesLocalizedData = $this->createInstance();
        $parameterValueData = $productParameterValueData->parameterValueData;
        $parameter = $productParameterValueData->parameter;

        $productParameterValuesLocalizedData->valueTextsByLocale[$parameterValueData->locale] = $parameterValueData->text;
        $productParameterValuesLocalizedData->parameter = $parameter;

        if ($parameter->isSlider()) {
            $productParameterValuesLocalizedData->numericValue = $parameterValueData->text;
        }

        return $productParameterValuesLocalizedData;
    }
}
