<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterValueDataFactoryInterface
{
    public function create(): ParameterValueData;

    public function createFromParameterValue(ParameterValue $parameterValue): ParameterValueData;
}
