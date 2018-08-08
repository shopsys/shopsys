<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterFactoryInterface
{

    public function create(ParameterData $data): Parameter;
}
