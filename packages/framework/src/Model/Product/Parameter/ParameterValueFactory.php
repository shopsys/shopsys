<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterValueFactory implements ParameterValueFactoryInterface
{

    public function create(ParameterValueData $data): ParameterValue
    {
        return new ParameterValue($data);
    }
}
