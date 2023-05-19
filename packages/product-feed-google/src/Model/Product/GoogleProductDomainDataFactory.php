<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

class GoogleProductDomainDataFactory implements GoogleProductDomainDataFactoryInterface
{
    /**
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData
     */
    protected function createInstance(): GoogleProductDomainData
    {
        return new GoogleProductDomainData();
    }

    /**
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomainData
     */
    public function create(): GoogleProductDomainData
    {
        return $this->createInstance();
    }
}
