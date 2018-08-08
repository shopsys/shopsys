<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ProductParameterValueDataFactoryInterface
{
    public function create(): ProductParameterValueData;

    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ProductParameterValueData;
}
