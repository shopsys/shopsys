<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

class ProductListUserErrorCodeHelper
{
    public static function getUserErrorCode(string $productListType, string $code): string
    {
        return $productListType . '-' . $code;
    }
}
