<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory as BaseProductFilterOptionsFactory;

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
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterValueFilterOption[] $parameterValueFilterOptions
     * @return \App\FrontendApi\Model\Product\Filter\ParameterFilterOption
     */
    protected function createParameterFilterOption(BaseParameter $parameter, array $parameterValueFilterOptions): ParameterFilterOption
    {
        return new ParameterFilterOption($parameter, $parameterValueFilterOptions);
    }
}
