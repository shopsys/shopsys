<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

interface ZboziProductDomainDataFactoryInterface
{
    public function create(): ZboziProductDomainData;
}
