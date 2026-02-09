<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

class HeurekaProductDomainDataFactory
{
    protected function createInstance(): HeurekaProductDomainData
    {
        return new HeurekaProductDomainData();
    }

    public function create(): HeurekaProductDomainData
    {
        return $this->createInstance();
    }
}
