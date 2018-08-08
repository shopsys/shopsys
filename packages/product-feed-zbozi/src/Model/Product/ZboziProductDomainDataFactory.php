<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

class ZboziProductDomainDataFactory implements ZboziProductDomainDataFactoryInterface
{
    public function create(): ZboziProductDomainData
    {
        return new ZboziProductDomainData();
    }
}
