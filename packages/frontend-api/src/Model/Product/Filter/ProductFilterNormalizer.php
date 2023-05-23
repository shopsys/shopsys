<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ProductFilterNormalizer
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    public function removeExcessiveFilters(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        $this->removeExcessiveBrands($productFilterData, $productFilterConfig);
        $this->removeExcessiveFlags($productFilterData, $productFilterConfig);
        $this->removeExcessiveParametersAndValues($productFilterData, $productFilterConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    protected function removeExcessiveBrands(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        foreach ($productFilterData->brands as $key => $brand) {
            if (!in_array($brand, $productFilterConfig->getBrandChoices(), true)) {
                unset($productFilterData->brands[$key]);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    protected function removeExcessiveFlags(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        foreach ($productFilterData->flags as $key => $flag) {
            if (!in_array($flag, $productFilterConfig->getFlagChoices(), true)) {
                unset($productFilterData->flags[$key]);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    protected function removeExcessiveParametersAndValues(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        $this->removeExcessiveParameters($productFilterData, $productFilterConfig);
        $this->removeExcessiveParameterValues($productFilterData, $productFilterConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    protected function removeExcessiveParameters(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        $parameters = $this->getAllParametersFromParameterFilterData($productFilterData->parameters);
        $parametersFromFilterConfig = $this->getAllParametersFromParameterFilterChoices(
            $productFilterConfig->getParameterChoices(),
        );

        foreach ($parameters as $key => $parameter) {
            if (!in_array($parameter, $parametersFromFilterConfig, true)) {
                unset($productFilterData->parameters[$key]);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     */
    protected function removeExcessiveParameterValues(
        ProductFilterData $productFilterData,
        ProductFilterConfig $productFilterConfig,
    ): void {
        $parameterValuesByParameterId = $this->getAllParameterValuesByParameterIdFromParameterFilterData(
            $productFilterData->parameters,
        );
        $parameterValuesByParameterIdFromFilterConfig = $this->getAllParameterValuesByParameterIdFromParameterFilterChoices(
            $productFilterConfig->getParameterChoices(),
        );

        foreach ($parameterValuesByParameterId as $parameterId => $parameterValues) {
            $parameterValuesFromFilterConfig = $parameterValuesByParameterIdFromFilterConfig[$parameterId];

            foreach ($parameterValues as $parameterValue) {
                if (!in_array($parameterValue, $parameterValuesFromFilterConfig, true)) {
                    $this->removeParameterValue($productFilterData, $parameterId, $parameterValue);
                }
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parametersFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    protected function getAllParametersFromParameterFilterData(array $parametersFilterData): array
    {
        $parameters = [];

        foreach ($parametersFilterData as $parameterFilterData) {
            $parameters[] = $parameterFilterData->parameter;
        }

        return $parameters;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    protected function getAllParametersFromParameterFilterChoices(array $parameterFilterChoices): array
    {
        $parameters = [];

        foreach ($parameterFilterChoices as $parameterFilterChoice) {
            $parameters[] = $parameterFilterChoice->getParameter();
        }

        return $parameters;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[] $parametersFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[][]
     */
    protected function getAllParameterValuesByParameterIdFromParameterFilterData(array $parametersFilterData): array
    {
        $parameterValues = [];

        foreach ($parametersFilterData as $parameterFilterData) {
            foreach ($parameterFilterData->values as $parameterValue) {
                $parameterValues[$parameterFilterData->parameter->getId()][] = $parameterValue;
            }
        }

        return $parameterValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[][]
     */
    protected function getAllParameterValuesByParameterIdFromParameterFilterChoices(
        array $parameterFilterChoices,
    ): array {
        $parameterValues = [];

        foreach ($parameterFilterChoices as $parameterFilterChoice) {
            foreach ($parameterFilterChoice->getValues() as $parameterValue) {
                $parameterValues[$parameterFilterChoice->getParameter()->getId()][] = $parameterValue;
            }
        }

        return $parameterValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param int $parameterId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     */
    protected function removeParameterValue(
        ProductFilterData $productFilterData,
        int $parameterId,
        ParameterValue $parameterValue,
    ): void {
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if ($parameterFilterData->parameter->getId() === $parameterId) {
                foreach ($parameterFilterData->values as $key => $filterParameterValue) {
                    if ($filterParameterValue === $parameterValue) {
                        unset($parameterFilterData->values[$key]);
                        break;
                    }
                }
            }
        }
    }
}
