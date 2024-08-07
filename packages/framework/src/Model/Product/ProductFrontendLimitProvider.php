<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductFrontendLimitProvider
{
    /**
     * @param int $productsFrontendLimit
     */
    public function __construct(
        protected int $productsFrontendLimit = 30,
    ) {
    }

    /**
     * @return int
     */
    public function getProductsFrontendLimit(): int
    {
        return $this->productsFrontendLimit;
    }
}
