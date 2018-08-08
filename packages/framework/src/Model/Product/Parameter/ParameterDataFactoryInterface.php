<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterDataFactoryInterface
{
    public function create(): ParameterData;

    public function createFromParameter(Parameter $parameter): ParameterData;
}
