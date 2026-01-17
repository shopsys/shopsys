<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ParameterValueConversionDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    protected function createInstance(): ParameterValueConversionData
    {
        return new ParameterValueConversionData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionData[]
     */
    public function createForNumericConversion(array $parameterValues): array
    {
        $parameterValueConversionsData = [];

        foreach ($parameterValues as $parameterValue) {
            $parameterValueConversionData = $this->createInstance();
            $numericValue = $this->convertStringToNumericValue($parameterValue->getText());

            $parameterValueConversionData->oldValueText = $parameterValue->getText();
            $parameterValueConversionData->newValueText = $numericValue;
            $parameterValueConversionData->locale = $parameterValue->getLocale();

            $parameterValueConversionsData[$parameterValue->getId()] = $parameterValueConversionData;
        }

        return $parameterValueConversionsData;
    }

    protected function convertStringToNumericValue(string $value): string
    {
        $value = str_replace([',', ' '], ['.', ''], $value);
        $value = preg_replace('/[^0-9.]/', '', $value);

        return $value ?? '0';
    }
}
