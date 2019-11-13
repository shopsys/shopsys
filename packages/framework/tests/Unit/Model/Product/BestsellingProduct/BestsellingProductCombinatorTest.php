<?php

namespace Tests\FrameworkBundle\Unit\Model\Product\BestsellingProduct;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductCombinator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class BestsellingProductCombinatorTest extends TestCase
{
    public function testCombineManualAndAutomaticBestsellingProducts()
    {
        $bestsellingProductCombinator = new BestsellingProductCombinator();

        $maxResults = 4;

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $productData = new ProductData();
        $productData->name = ['cs' => 'Product 1'];
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

        $combinedProducts = $bestsellingProductCombinator->combineManualAndAutomaticProducts(
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
