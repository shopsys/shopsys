<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

interface HeurekaProductDomainDataFactoryInterface
{
    public function create(): HeurekaProductDomainData;
}
