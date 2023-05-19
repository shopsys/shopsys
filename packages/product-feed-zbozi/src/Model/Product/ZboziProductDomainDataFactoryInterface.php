<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

interface ZboziProductDomainDataFactoryInterface
{
    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData
     */
    public function create(): ZboziProductDomainData;
}
