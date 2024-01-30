<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class StockFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockData $stockData
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock
     */
    public function create(StockData $stockData): Stock
    {
        $entityClassName = $this->entityNameResolver->resolve(Stock::class);

        return new $entityClassName($stockData);
    }
}
