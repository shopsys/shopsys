<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

class ProductListUserErrorCodeHelper
{
    /**
     * @param string $productListType
     * @param string $code
     * @return string
     */
    public static function getUserErrorCode(string $productListType, string $code): string
    {
        return $productListType . '-' . $code;
    }
}
