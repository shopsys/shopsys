<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use Iterator;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;

class ProductListTypesDataProvider
{
    /**
     * @return \Iterator
     */
    public static function getProductListTypes(): Iterator
    {
        foreach (ReflectionHelper::getAllPublicClassConstants(ProductListTypeEnum::class) as $productListType) {
            yield [$productListType];
        }
    }
}
