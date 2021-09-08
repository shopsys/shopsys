<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade, \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption createFlagFilterOption(\App\Model\Product\Flag\Flag $flag, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption createBrandFilterOption(\App\Model\Product\Brand\Brand $brand, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptions(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForAll(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForCategory(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
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
}
