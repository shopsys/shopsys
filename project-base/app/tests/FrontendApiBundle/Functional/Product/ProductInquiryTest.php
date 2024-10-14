<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductInquiryTest extends GraphQlTestCase
{
    public function testProductInquiryHasProperType(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '45', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/productByUuid.graphql', [
            'uuid' => $product->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $this->assertSame('INQUIRY', $data['productType']);

        $expectedProductPrice = [
            'isPriceFrom' => false,
            'priceWithoutVat' => '***',
            'priceWithVat' => '***',
            'vatAmount' => '***',
        ];
        $this->assertSame($expectedProductPrice, $data['price']);
    }
}
