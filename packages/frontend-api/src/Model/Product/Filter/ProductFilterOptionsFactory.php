<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;

class ProductFilterOptionsFactory
{
    public function createProductFilterOptionsInstance(): ProductFilterOptions
    {
        return new ProductFilterOptions();
    }

    protected function createFlagFilterOption(
        Flag $flag,
        int $count,
        bool $isAbsolute,
        bool $isSelected = false,
    ): FlagFilterOption {
        return new FlagFilterOption($flag, $count, $isAbsolute, $isSelected);
    }

    protected function createBrandFilterOption(Brand $brand, int $count, bool $isAbsolute): BrandFilterOption
    {
        return new BrandFilterOption($brand, $count, $isAbsolute);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions
     */
    protected function createParameterFilterOption(
        Parameter $parameter,
        array $parameterValueFilterOptions,
        bool $collapsed,
        bool $isSliderAllowed,
        ?float $selectedValue = null,
    ): ParameterFilterOption {
        return new ParameterFilterOption($parameter, $parameterValueFilterOptions, $collapsed, $isSliderAllowed, $selectedValue);
    }

    protected function createParameterValueFilterOption(
        ParameterValue $parameterValue,
        int $count,
        bool $isAbsolute,
        bool $isSelected = false,
    ): ParameterValueFilterOption {
        return new ParameterValueFilterOption($parameterValue, $count, $isAbsolute, $isSelected);
    }

    public function createProductFilterOptions(
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): ProductFilterOptions {
        $productFilterOptions = $this->createProductFilterOptionsInstance();
        $productFilterOptions->minimalPrice = $productFilterConfig->getPriceRange()->getMinimalPrice();
        $productFilterOptions->maximalPrice = $productFilterConfig->getPriceRange()->getMaximalPrice();

        $productFilterOptions->inStock = $productFilterCountData->countInStock ?? 0;

        $this->fillFlags($productFilterOptions, $productFilterConfig, $productFilterCountData, $productFilterData, $readyCategorySeoMix);

        return $productFilterOptions;
    }

    public function createFullProductFilterOptions(
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
    ): ProductFilterOptions {
        $productFilterOptions = $this->createProductFilterOptions($productFilterConfig, $productFilterCountData, $productFilterData);
        $this->fillBrands($productFilterOptions, $productFilterConfig, $productFilterCountData, $productFilterData);
        $this->fillParameters($productFilterOptions, $productFilterConfig, $productFilterCountData, $productFilterData);

        return $productFilterOptions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] $collapsedParameters
     */
    public function createProductFilterOptionsByBatchLoadData(
        ProductFilterOptionsBatchLoadData $batchLoadData,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        array $collapsedParameters,
    ): ProductFilterOptions {
        $entity = $batchLoadData->getEntity();
        $productFilterData = $batchLoadData->getProductFilterData();
        $readyCategorySeoMix = $batchLoadData->getReadyCategorySeoMix();

        $productFilterOptions = $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
            $readyCategorySeoMix,
        );

        if ($entity instanceof Brand) {
            return $productFilterOptions;
        }

        $this->fillBrands($productFilterOptions, $productFilterConfig, $productFilterCountData, $productFilterData);

        if ($entity instanceof Category) {
            $this->fillParametersForCategory(
                $productFilterOptions,
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
                $collapsedParameters,
                $readyCategorySeoMix,
            );
        }

        return $productFilterOptions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] $collapsedParameters
     */
    protected function fillParametersForCategory(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
        array $collapsedParameters,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): void {
        foreach ($productFilterConfig->getParameterChoices() as $parameterFilterChoice) {
            $parameter = $parameterFilterChoice->getParameter();
            $isAbsolute = !$this->isParameterFiltered($parameter, $productFilterData);

            $parameterValueFilterOptions = [];

            $isSliderSelectable = false;

            foreach ($parameterFilterChoice->getValues() as $parameterValue) {
                $parameterValueCount = $this->getParameterValueCount(
                    $parameter,
                    $parameterValue,
                    $productFilterData,
                    $productFilterCountData,
                );

                if ($parameterValueCount > 0 && $parameter->isSlider()) {
                    $isSliderSelectable = true;
                }

                $parameterValueFilterOptions[] = $this->createParameterValueFilterOption(
                    $parameterValue,
                    $parameterValueCount,
                    $isAbsolute,
                    $this->isParameterValueSelected($readyCategorySeoMix, $parameter, $parameterValue),
                );
            }

            $parameterFilterOption = $this->createParameterFilterOption(
                $parameter,
                $parameterValueFilterOptions,
                in_array($parameter, $collapsedParameters, true),
                $isSliderSelectable,
                $this->getParameterSelectedValue($readyCategorySeoMix, $parameterFilterChoice),
            );

            if ($parameterFilterOption->parameter->isSlider() !== false && $parameterFilterOption->minimalValue === 0.0 && $parameterFilterOption->maximalValue === 0.0) {
                continue;
            }

            $productFilterOptions->parameters[] = $parameterFilterOption;
        }
    }

    protected function isParameterValueSelected(
        ?ReadyCategorySeoMix $readyCategorySeoMix,
        Parameter $parameter,
        BaseParameterValue $parameterValue,
    ): bool {
        if ($readyCategorySeoMix === null) {
            return false;
        }

        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $categorySeoMixParameterParameterValue) {
            if ($categorySeoMixParameterParameterValue->getParameter() === $parameter && $categorySeoMixParameterParameterValue->getParameterValue() === $parameterValue) {
                return true;
            }
        }

        return false;
    }

    protected function getParameterSelectedValue(
        ?ReadyCategorySeoMix $readyCategorySeoMix,
        ParameterFilterChoice $parameterFilterChoice,
    ): ?float {
        if ($readyCategorySeoMix === null) {
            return null;
        }

        foreach ($readyCategorySeoMix->getReadyCategorySeoMixParameterParameterValues() as $categorySeoMixParameterParameterValue) {
            if ($categorySeoMixParameterParameterValue->getParameter()->isSlider()
                && $categorySeoMixParameterParameterValue->getParameter() === $parameterFilterChoice->getParameter()
                && in_array($categorySeoMixParameterParameterValue->getParameterValue(), $parameterFilterChoice->getValues(), true)
            ) {
                return (float)$categorySeoMixParameterParameterValue->getParameterValue()->getText();
            }
        }

        return null;
    }

    protected function fillFlags(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix,
    ): void {
        $isAbsolute = count($productFilterData->flags) === 0;

        foreach ($productFilterConfig->getFlagChoices() as $flag) {
            $productFilterOptions->flags[] = $this->createFlagFilterOption(
                $flag,
                $productFilterCountData->countByFlagId[$flag->getId()] ?? 0,
                $isAbsolute,
                $readyCategorySeoMix !== null && $readyCategorySeoMix->getFlag() === $flag,
            );
        }
    }

    protected function fillBrands(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
    ): void {
        $isAbsolute = count($productFilterData->brands) === 0;

        foreach ($productFilterConfig->getBrandChoices() as $brand) {
            $productFilterOptions->brands[] = $this->createBrandFilterOption(
                $brand,
                $productFilterCountData->countByBrandId[$brand->getId()] ?? 0,
                $isAbsolute,
            );
        }
    }

    protected function fillParameters(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
    ): void {
        foreach ($productFilterConfig->getParameterChoices() as $parameterFilterChoice) {
            $parameter = $parameterFilterChoice->getParameter();
            $isAbsolute = !$this->isParameterFiltered($parameter, $productFilterData);

            $parameterValueFilterOptions = [];

            $isSliderSelectable = false;

            foreach ($parameterFilterChoice->getValues() as $parameterValue) {
                $parameterValueCount = $this->getParameterValueCount(
                    $parameter,
                    $parameterValue,
                    $productFilterData,
                    $productFilterCountData,
                );

                if ($parameterValueCount > 0 && $parameter->isSlider()) {
                    $isSliderSelectable = true;
                }

                $parameterValueFilterOptions[] = $this->createParameterValueFilterOption(
                    $parameterValue,
                    $parameterValueCount,
                    $isAbsolute,
                    false,
                );
            }

            $productFilterOptions->parameters[] = $this->createParameterFilterOption(
                $parameter,
                $parameterValueFilterOptions,
                false,
                $isSliderSelectable,
            );
        }
    }

    protected function isParameterFiltered(Parameter $parameter, ProductFilterData $productFilterData): bool
    {
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if ($parameterFilterData->parameter === $parameter) {
                return true;
            }
        }

        return false;
    }

    protected function isParameterValueFiltered(
        Parameter $parameter,
        ParameterValue $parameterValue,
        ProductFilterData $productFilterData,
    ): bool {
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if ($parameterFilterData->parameter === $parameter && in_array($parameterValue, $parameterFilterData->values, true)) {
                return true;
            }
        }

        return false;
    }

    protected function getParameterValueCount(
        Parameter $parameter,
        ParameterValue $parameterValue,
        ProductFilterData $productFilterData,
        ProductFilterCountData $productFilterCountData,
    ): int {
        if ($this->isParameterValueFiltered($parameter, $parameterValue, $productFilterData)) {
            return 0;
        }

        return $productFilterCountData->countByParameterIdAndValueId[$parameter->getId()][$parameterValue->getId()] ?? 0;
    }
}
