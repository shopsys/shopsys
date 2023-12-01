<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use Iterator;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;

class ProductListTypesDataProvider
{
    /**
     * @return \Iterator
     */
    public static function getProductListTypes(): Iterator
    {
        foreach (ProductListTypeEnum::cases() as $productListType) {
            yield [$productListType];
        }
    }
}
