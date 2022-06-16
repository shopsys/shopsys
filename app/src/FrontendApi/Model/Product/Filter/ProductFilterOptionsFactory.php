<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Category\Category;
use App\Model\Category\CategoryParameterFacade;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterValue;
use App\Model\Product\ProductOnCurrentDomainElasticFacade;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData as BaseProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption as BaseFlagFilterOption;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption createBrandFilterOption(\App\Model\Product\Brand\Brand $brand, int $count, bool $isAbsolute)
 * @method fillBrands(\Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterFiltered(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterValueFiltered(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue $parameterValue, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method int getParameterValueCount(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue $parameterValue, \App\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData)
 */
class ProductFilterOptionsFactory extends BaseProductFilterOptionsFactory
{
    /**
     * @var \App\Model\Category\CategoryParameterFacade
     */
    private CategoryParameterFacade $categoryParameterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     */
    public function __construct(
        ModuleFacade $moduleFacade,
        ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade,
        CategoryParameterFacade $categoryParameterFacade
    ) {
        parent::__construct($moduleFacade, $productOnCurrentDomainFacade);

        $this->categoryParameterFacade = $categoryParameterFacade;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue $brand
     * @param int $count
     * @param bool $isAbsolute
     * @param bool $isSelected
     * @return \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption
     */
    protected function createParameterValueFilterOption(BaseParameterValue $brand, int $count, bool $isAbsolute, bool $isSelected = false): ParameterValueFilterOption
    {
        return new ParameterValueFilterOption($brand, $count, $isAbsolute, $isSelected);
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions
     * @param bool $collapsed
     * @param float|null $selectedValue
     * @return \App\FrontendApi\Model\Product\Filter\ParameterFilterOption
     */
    protected function createParameterFilterOption(
        BaseParameter $parameter,
        array $parameterValueFilterOptions,
        bool $collapsed = false,
        ?float $selectedValue = null
    ): ParameterFilterOption {
        return new ParameterFilterOption($parameter, $parameterValueFilterOptions, $collapsed, $selectedValue);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForFlag(
        Flag $flag,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataForFlag(
            $flag->getId(),
            $productFilterData,
            $searchText
        );

        $productFilterOptions = $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData
        );
        $this->fillBrands($productFilterOptions, $productFilterConfig, $productFilterCountData, $productFilterData);

        return $productFilterOptions;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForAll(
        ProductFilterConfig $productFilterConfig,
        BaseProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        if ($searchText !== '') {
            $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataForSearch(
                $searchText,
                $productFilterConfig,
                $productFilterData
            );
        } else {
            $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataForAll(
                $productFilterData
            );
        }

        $productFilterOptions = $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData
        );
        $this->fillBrands(
            $productFilterOptions,
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData
        );

        return $productFilterOptions;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForCategory(
        BaseCategory $category,
        ProductFilterConfig $productFilterConfig,
        BaseProductFilterData $productFilterData,
        string $searchText = '',
        ?ReadyCategorySeoMix $readyCategorySeoMix = null
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataInCategory(
            $category->getId(),
            $productFilterConfig,
            $productFilterData,
            $searchText
        );

        $productFilterOptions = $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
            $readyCategorySeoMix
        );
        $this->fillBrands(
            $productFilterOptions,
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData
        );
        $this->fillParameters(
            $productFilterOptions,
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
            $category,
            $readyCategorySeoMix
        );

        return $productFilterOptions;
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForBrand(
        Brand $brand,
        ProductFilterConfig $productFilterConfig,
        BaseProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataForBrand(
            $brand->getId(),
            $productFilterData,
            $searchText
        );

        return $this->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData
        );
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\Category\Category|null $category
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    protected function fillParameters(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        BaseProductFilterData $productFilterData,
        ?Category $category = null,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null
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

            foreach ($parameterFilterChoice->getValues() as $parameterValue) {
                /** @var \App\Model\Product\Parameter\ParameterValue $parameterValue */
                $parameterValueCount = $this->getParameterValueCount(
                    $parameter,
                    $parameterValue,
                    $productFilterData,
                    $productFilterCountData
                );
                $parameterValueFilterOptions[] = $this->createParameterValueFilterOption(
                    $parameterValue,
                    $parameterValueCount,
                    $isAbsolute,
                    $this->isParameterValueSelected($readyCategorySeoMix, $parameter, $parameterValue)
                );
            }

            $productFilterOptions->parameters[] = $this->createParameterFilterOption(
                $parameter,
                $parameterValueFilterOptions,
                in_array($parameter, $collapsedParameters, true),
                $this->getParameterSelectedValue($readyCategorySeoMix, $parameterFilterChoice)
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    protected function createProductFilterOptions(
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        BaseProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null
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
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     */
    protected function fillFlags(
        ProductFilterOptions $productFilterOptions,
        ProductFilterConfig $productFilterConfig,
        ProductFilterCountData $productFilterCountData,
        BaseProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null
    ): void {
        $isAbsolute = count($productFilterData->flags) === 0;

        /** @var \App\Model\Product\Flag\Flag $flag */
        foreach ($productFilterConfig->getFlagChoices() as $flag) {
            $productFilterOptions->flags[] = $this->createFlagFilterOption(
                $flag,
                $productFilterCountData->countByFlagId[$flag->getId()] ?? 0,
                $isAbsolute,
                $readyCategorySeoMix !== null && $readyCategorySeoMix->getFlag() === $flag
            );
        }
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param int $count
     * @param bool $isAbsolute
     * @param bool $isSelected
     * @return \App\FrontendApi\Model\Product\Filter\FlagFilterOption
     */
    protected function createFlagFilterOption(BaseFlag $flag, int $count, bool $isAbsolute, bool $isSelected = false): BaseFlagFilterOption
    {
        return new FlagFilterOption($flag, $count, $isAbsolute, $isSelected);
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     * @return bool
     */
    private function isParameterValueSelected(?ReadyCategorySeoMix $readyCategorySeoMix, Parameter $parameter, ParameterValue $parameterValue): bool
    {
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
    private function getParameterSelectedValue(?ReadyCategorySeoMix $readyCategorySeoMix, ParameterFilterChoice $parameterFilterChoice): ?float
    {
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
