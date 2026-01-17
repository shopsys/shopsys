<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValueDataFactory
{
    public function __construct(
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly ParameterFacade $parameterFacade,
    ) {
    }

    protected function createInstance(): ProductParameterValueData
    {
        return new ProductParameterValueData();
    }

    public function create(): ProductParameterValueData
    {
        return $this->createInstance();
    }

    public function createFromProductParameterValue(
        ProductParameterValue $productParameterValue,
    ): ProductParameterValueData {
        $productParameterValueData = $this->createInstance();
        $this->fillFromProductParameterValue($productParameterValueData, $productParameterValue);

        return $productParameterValueData;
    }

    protected function fillFromProductParameterValue(
        ProductParameterValueData $productParameterValueData,
        ProductParameterValue $productParameterValue,
    ) {
        $productParameterValueData->parameter = $productParameterValue->getParameter();
        $productParameterValueData->parameterValueData = $this->parameterValueDataFactory->createFromParameterValue(
            $productParameterValue->getValue(),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    public function createMultipleFromProductParameterValuesLocalizedData(
        ProductParameterValuesLocalizedData $productParameterValuesLocalizedData,
    ): array {
        $productParameterValuesData = [];

        foreach ($productParameterValuesLocalizedData->valueTextsByLocale as $locale => $valueText) {
            if ($valueText !== null) {
                $isSliderWithNumericValue = $productParameterValuesLocalizedData->parameter->isSlider() && is_numeric($valueText);
                $productParameterValueData = $this->create();
                $productParameterValueData->parameter = $productParameterValuesLocalizedData->parameter;

                $parameterValue = $this->parameterFacade->findParameterValueByValueTextNumericValueAndLocale($valueText, $isSliderWithNumericValue ? $valueText : null, $locale);

                if ($parameterValue === null) {
                    $parameterValueData = $this->parameterValueDataFactory->create();
                } else {
                    $parameterValueData = $this->parameterValueDataFactory->createFromParameterValue($parameterValue);
                }
                $parameterValueData->text = $valueText;

                if ($isSliderWithNumericValue) {
                    $parameterValueData->numericValue = $valueText;
                }

                $parameterValueData->locale = $locale;
                $productParameterValueData->parameterValueData = $parameterValueData;

                $productParameterValuesData[] = $productParameterValueData;
            }
        }

        return $productParameterValuesData;
    }
}
