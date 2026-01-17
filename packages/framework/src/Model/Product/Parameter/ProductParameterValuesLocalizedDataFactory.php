<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValuesLocalizedDataFactory
{
    protected function createInstance(): ProductParameterValuesLocalizedData
    {
        return new ProductParameterValuesLocalizedData();
    }

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
