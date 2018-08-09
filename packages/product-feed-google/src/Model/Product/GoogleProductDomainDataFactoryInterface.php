<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

interface GoogleProductDomainDataFactoryInterface
{
    public function create(): GoogleProductDomainData;
}
