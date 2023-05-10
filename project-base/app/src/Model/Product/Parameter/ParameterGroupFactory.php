<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

class ParameterGroupFactory
{
    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @return \App\Model\Product\Parameter\ParameterGroup
     */
    public function create(ParameterGroupData $parameterGroupData): ParameterGroup
    {
        return new ParameterGroup($parameterGroupData);
    }
}
