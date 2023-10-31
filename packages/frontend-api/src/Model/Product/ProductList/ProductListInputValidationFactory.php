<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnumInterface;

class ProductListInputValidationFactory
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductList\WishlistInputValidator $productListInputValidator
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductList\ComparisonInputValidator $comparisonInputValidator
     */
    public function __construct(
        protected readonly WishlistInputValidator $productListInputValidator,
        protected readonly ComparisonInputValidator $comparisonInputValidator,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     * @return \Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListInputValidatorInterface
     */
    public function createForProductListType(
        ProductListTypeEnumInterface $productListType,
    ): ProductListInputValidatorInterface {
        return match ($productListType) {
            ProductListTypeEnum::WISHLIST => $this->productListInputValidator,
            ProductListTypeEnum::COMPARISON => $this->comparisonInputValidator,
        };
    }
}
