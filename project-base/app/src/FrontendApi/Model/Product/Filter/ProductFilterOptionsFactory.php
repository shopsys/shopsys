<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Parameter\Parameter;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption createFlagFilterOption(\App\Model\Product\Flag\Flag $flag, int $count, bool $isAbsolute, bool $isSelected = false)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption createBrandFilterOption(\App\Model\Product\Brand\Brand $brand, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption createParameterFilterOption(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions, bool $collapsed, bool $isSliderAllowed, float|null $selectedValue = null)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForBrand(\App\Model\Product\Brand\Brand $brand, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterFiltered(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterValueFiltered(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method int getParameterValueCount(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForFlag(\App\Model\Product\Flag\Flag $flag, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 */
class ProductFilterOptionsFactory extends BaseProductFilterOptionsFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
     */
    public function __construct(
        protected readonly CategoryParameterFacade $categoryParameterFacade,
        ModuleFacade $moduleFacade,
        BaseProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
    ) {
        parent::__construct($moduleFacade, $productOnCurrentDomainElasticFacade);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForCategory(
        BaseCategory $category,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataInCategory(
            $category->getId(),
            $productFilterConfig,
            $productFilterData,
        );

        $productFilterOptions = $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
            $readyCategorySeoMix,
        );
        $this->fillBrands(
            $productFilterOptions,
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
        );
        $this->fillParametersForCategory(
            $productFilterOptions,
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
            $category,
            $readyCategorySeoMix,
        );

        return $productFilterOptions;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category|null $category
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    protected function fillParametersForCategory(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
        ?Category $category = null,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): void {
        if ($category === null) {
            throw new InvalidArgumentException('$category parameter must be provided');
        }
        $collapsedParameters = $this->categoryParameterFacade->getParametersCollapsedByCategory($category);

        foreach ($productFilterConfig->getParameterChoices() as $parameterFilterChoice) {
            /** @var \App\Model\Product\Parameter\Parameter $parameter */
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

            $productFilterOptions->parameters[] = $this->createParameterFilterOption(
                $parameter,
                $parameterValueFilterOptions,
                in_array($parameter, $collapsedParameters, true),
                $isSliderSelectable,
                $this->getParameterSelectedValue($readyCategorySeoMix, $parameterFilterChoice),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
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

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    protected function fillFlags(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): void {
        $isAbsolute = count($productFilterData->flags) === 0;

        /** @var \App\Model\Product\Flag\Flag $flag */
        foreach ($productFilterConfig->getFlagChoices() as $flag) {
            $productFilterOptions->flags[] = $this->createFlagFilterOption(
                $flag,
                $productFilterCountData->countByFlagId[$flag->getId()] ?? 0,
                $isAbsolute,
                $readyCategorySeoMix !== null && $readyCategorySeoMix->getFlag() === $flag,
            );
        }
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @return bool
     */
    private function isParameterValueSelected(
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

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice $parameterFilterChoice
     * @return float|null
     */
    private function getParameterSelectedValue(
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
}
