<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductDeleteResult
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $productsForRecalculations
     */
    public function __construct(protected readonly array $productsForRecalculations = [])
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForRecalculations(): array
    {
        return $this->productsForRecalculations;
    }
}
