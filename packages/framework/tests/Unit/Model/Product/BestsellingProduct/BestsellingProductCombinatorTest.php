<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\BestsellingProduct;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductCombinator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class BestsellingProductCombinatorTest extends TestCase
{
    public function testCombineManualAndAutomaticBestsellingProducts()
    {
        $bestsellingProductCombinator = new BestsellingProductCombinator();

        $maxResults = 4;
        $productData = TestProductProvider::getTestProductData();
        $productData->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData);
        $product2 = Product::create($productData);
        $product3 = Product::create($productData);
        $product4 = Product::create($productData);
        $product5 = Product::create($productData);

        $manualProductsIndexedByPosition = [
            0 => $product1,
            2 => $product2,
        ];

        $automaticProducts = [
            $product1,
            $product3,
            $product4,
            $product5,
        ];

        $combinedProducts = $bestsellingProductCombinator->combineManualAndAutomaticProducts(
            $manualProductsIndexedByPosition,
            $automaticProducts,
            $maxResults,
        );

        $combinedProductsExpected = [
            0 => $product1,
            1 => $product3,
            2 => $product2,
            3 => $product4,
        ];

        $this->assertEquals($combinedProducts, $combinedProductsExpected);
    }
}
