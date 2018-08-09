<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterFactory implements ParameterFactoryInterface
{
    public function create(ParameterData $data): Parameter
    {
        return new Parameter($data);
    }
}
