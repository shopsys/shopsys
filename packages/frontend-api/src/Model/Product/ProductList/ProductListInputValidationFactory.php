<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Product\List\Exception\UnknownProductListTypeException;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;

class ProductListInputValidationFactory
{
    public function __construct(
        protected readonly WishlistInputValidator $productListInputValidator,
        protected readonly ComparisonInputValidator $comparisonInputValidator,
    ) {
    }

    public function createForProductListType(
        string $productListType,
    ): ProductListInputValidatorInterface {
        return match ($productListType) {
            ProductListTypeEnum::WISHLIST => $this->productListInputValidator,
            ProductListTypeEnum::COMPARISON => $this->comparisonInputValidator,
            default => throw new UnknownProductListTypeException($productListType),
        };
    }
}
