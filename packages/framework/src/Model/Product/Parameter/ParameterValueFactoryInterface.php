<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterValueFactoryInterface
{
    public function create(ParameterValueData $data): ParameterValue;
}
