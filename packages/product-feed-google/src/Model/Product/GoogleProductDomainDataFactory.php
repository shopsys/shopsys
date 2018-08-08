<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

class GoogleProductDomainDataFactory implements GoogleProductDomainDataFactoryInterface
{
    public function create(): GoogleProductDomainData
    {
        return new GoogleProductDomainData();
    }
}
