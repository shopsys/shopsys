<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValueDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    protected function createInstance(): ProductParameterValueData
    {
        return new ProductParameterValueData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function create(): ProductParameterValueData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function createFromProductParameterValue(
        ProductParameterValue $productParameterValue,
    ): ProductParameterValueData {
        $productParameterValueData = $this->createInstance();
        $this->fillFromProductParameterValue($productParameterValueData, $productParameterValue);

        return $productParameterValueData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     */
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData $productParameterValuesLocalizedData
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
