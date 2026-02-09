<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class StockFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(StockData $stockData): Stock
    {
        $entityClassName = $this->entityNameResolver->resolve(Stock::class);

        return new $entityClassName($stockData);
    }
}
