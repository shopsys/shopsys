<?php

namespace Tests\ShopBundle\Unit\Model\Product\BestsellingProduct;

use PHPUnit_Framework_TestCase;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductService;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class BestsellingProductServiceTest extends PHPUnit_Framework_TestCase
{
    public function testCombineManualAndAutomaticBestsellingProducts()
    {
        $bestsellingProductService = new BestsellingProductService();

        $maxResults = 4;

        $price = 100;
        $vat = new Vat(new VatData('vat', 21));
        $productData = new ProductData();
        $productData->name = ['cs' => 'Product 1'];
        $productData->price = $price;
        $productData->vat = $vat;
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

        $combinedProducts = $bestsellingProductService->combineManualAndAutomaticProducts(
            $manualProductsIndexedByPosition,
            $automaticProducts,
            $maxResults
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
