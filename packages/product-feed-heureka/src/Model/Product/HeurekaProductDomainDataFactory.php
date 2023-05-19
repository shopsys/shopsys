<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

class HeurekaProductDomainDataFactory implements HeurekaProductDomainDataFactoryInterface
{
    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData
     */
    protected function createInstance(): HeurekaProductDomainData
    {
        return new HeurekaProductDomainData();
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomainData
     */
    public function create(): HeurekaProductDomainData
    {
        return $this->createInstance();
    }
}
