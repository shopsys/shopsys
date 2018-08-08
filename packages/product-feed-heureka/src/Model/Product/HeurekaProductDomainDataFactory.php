<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

class HeurekaProductDomainDataFactory implements HeurekaProductDomainDataFactoryInterface
{
    public function create(): HeurekaProductDomainData
    {
        return new HeurekaProductDomainData();
    }
}
