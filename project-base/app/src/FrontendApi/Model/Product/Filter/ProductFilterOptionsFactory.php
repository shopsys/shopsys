<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

/**
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption createFlagFilterOption(\App\Model\Product\Flag\Flag $flag, int $count, bool $isAbsolute, bool $isSelected = false)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption createBrandFilterOption(\App\Model\Product\Brand\Brand $brand, int $count, bool $isAbsolute)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption createParameterFilterOption(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions, bool $collapsed, bool $isSliderAllowed, float|null $selectedValue = null)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForBrand(\App\Model\Product\Brand\Brand $brand, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterFiltered(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method bool isParameterValueFiltered(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method int getParameterValueCount(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForFlag(\App\Model\Product\Flag\Flag $flag, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions createProductFilterOptionsForCategory(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix = null)
 * @method fillParametersForCategory(\Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions $productFilterOptions, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $productFilterCountData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix = null)
 * @method bool isParameterValueSelected(\Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix, \App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue)
 */
class ProductFilterOptionsFactory extends BaseProductFilterOptionsFactory
{
}
