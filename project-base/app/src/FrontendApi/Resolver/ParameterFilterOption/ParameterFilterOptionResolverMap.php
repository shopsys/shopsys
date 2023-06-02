<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\ParameterFilterOption;

use App\FrontendApi\Model\Product\Filter\ParameterFilterOption;
use App\FrontendApi\Resolver\ParameterFilterOption\Exception\TypeNotImplementedException;
use App\Model\Product\Parameter\Parameter;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class ParameterFilterOptionResolverMap extends ResolverMap
{
    private const PARAMETER_CHECKBOX_FILTER_OPTION = 'ParameterCheckboxFilterOption';
    private const PARAMETER_SLIDER_FILTER_OPTION = 'ParameterSliderFilterOption';
    private const PARAMETER_COLOR_FILTER_OPTION = 'ParameterColorFilterOption';

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
     * @param \App\FrontendApi\Model\Product\Filter\ParameterFilterOption $parameterFilterOption
     * @return string
     */
    private function getResolveType(ParameterFilterOption $parameterFilterOption): string
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
