<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductFrontendLimitProvider
{
    public function __construct(
        protected int $productsFrontendLimit = 30,
    ) {
    }

    public function getProductsFrontendLimit(): int
    {
        return $this->productsFrontendLimit;
    }
}
