<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

class GoogleProductDomainDataFactory
{
    protected function createInstance(): GoogleProductDomainData
    {
        return new GoogleProductDomainData();
    }

    public function create(): GoogleProductDomainData
    {
        return $this->createInstance();
    }
}
