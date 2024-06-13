<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\ParameterFilterOption;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption;
use Shopsys\FrontendApiBundle\Model\Resolver\ParameterFilterOption\Exception\TypeNotImplementedException;

class ParameterFilterOptionResolverMap extends ResolverMap
{
    protected const string PARAMETER_CHECKBOX_FILTER_OPTION = 'ParameterCheckboxFilterOption';
    protected const string PARAMETER_SLIDER_FILTER_OPTION = 'ParameterSliderFilterOption';
    protected const string PARAMETER_COLOR_FILTER_OPTION = 'ParameterColorFilterOption';

    /**
     * {@inheritdoc}
     */
    protected function map()
    {
        return [
            'ParameterFilterOptionInterface' => [
                self::RESOLVE_TYPE => function (ParameterFilterOption $parameterFilterOption) {
                    return $this->getResolveType($parameterFilterOption);
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption $parameterFilterOption
     * @return string
     */
    protected function getResolveType(ParameterFilterOption $parameterFilterOption): string
    {
        $parameterType = $parameterFilterOption->parameter->getParameterType();

        if ($parameterType === Parameter::PARAMETER_TYPE_COMMON) {
            return self::PARAMETER_CHECKBOX_FILTER_OPTION;
        }

        if ($parameterType === Parameter::PARAMETER_TYPE_SLIDER) {
            return self::PARAMETER_SLIDER_FILTER_OPTION;
        }

        if ($parameterType === Parameter::PARAMETER_TYPE_COLOR) {
            return self::PARAMETER_COLOR_FILTER_OPTION;
        }

        throw new TypeNotImplementedException($parameterType);
    }
}
