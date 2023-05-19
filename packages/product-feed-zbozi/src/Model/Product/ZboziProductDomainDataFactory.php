<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

class ZboziProductDomainDataFactory implements ZboziProductDomainDataFactoryInterface
{
    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData
     */
    protected function createInstance(): ZboziProductDomainData
    {
        return new ZboziProductDomainData();
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData
     */
    public function create(): ZboziProductDomainData
    {
        return $this->createInstance();
    }
}
