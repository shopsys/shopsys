<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

class ZboziProductDomainDataFactory
{
    protected function createInstance(): ZboziProductDomainData
    {
        return new ZboziProductDomainData();
    }

    public function create(): ZboziProductDomainData
    {
        return $this->createInstance();
    }
}
