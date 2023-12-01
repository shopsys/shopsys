<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnumInterface;

class ProductListUserErrorCodeHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     * @param string $code
     * @return string
     */
    public static function getUserErrorCode(ProductListTypeEnumInterface $productListType, string $code): string
    {
        return $productListType->value . '-' . $code;
    }
}
