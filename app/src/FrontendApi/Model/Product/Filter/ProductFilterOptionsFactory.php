<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData as BaseProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade, \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption createFlagFilterOption(\App\Model\Product\Flag\Flag $flag, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption createBrandFilterOption(\App\Model\Product\Brand\Brand $brand, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptions(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForBrand(\App\Model\Product\Brand\Brand $brand, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method fillFlags(\Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method fillBrands(\Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method fillParameters(\Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterFiltered(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterValueFiltered(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue $parameterValue, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method int getParameterValueCount(\App\Model\Product\Parameter\Parameter $parameter, \App\Model\Product\Parameter\ParameterValue $parameterValue, \App\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData)
 */
class ProductFilterOptionsFactory extends BaseProductFilterOptionsFactory
{
    /**
     * @param \App\Model\Product\Parameter\ParameterValue $brand
     * @param int $count
     * @param bool $isAbsolute
     * @return \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption
     */
    protected function createParameterValueFilterOption(BaseParameterValue $brand, int $count, bool $isAbsolute): ParameterValueFilterOption
    {
        return new ParameterValueFilterOption($brand, $count, $isAbsolute);
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\FrontendApi\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions
     * @return \App\FrontendApi\Model\Product\Filter\ParameterFilterOption
     */
    protected function createParameterFilterOption(BaseParameter $parameter, array $parameterValueFilterOptions): ParameterFilterOption
    {
        return new ParameterFilterOption($parameter, $parameterValueFilterOptions);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForFlag(
        Flag $flag,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData
    ): ProductFilterOptions {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->createProductFilterOptionsInstance();
        }

        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataForFlag(
            $flag->getId(),
            $productFilterData
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
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function createProductFilterOptionsForCategory(
        Category $category,
        ProductFilterConfig $productFilterConfig,
        BaseProductFilterData $productFilterData,
        string $searchText = ''
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
            $productFilterData
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
            $productFilterData
        );

        return $productFilterOptions;
    }
}
