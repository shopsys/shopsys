<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PromotionProductCartPriceTest extends GraphQlTestCase
{
    private Product $promotionProduct;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 154, Product::class);
    }

    #[DataProvider('promotionQuantitiesProvider')]
    public function testPromotionProductPriceIsCalculatedCorrectly(int $requestedQuantity, int $expectedFreebies): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->promotionProduct->getUuid(),
            'quantity' => $requestedQuantity,
        ]);

        $addToCartData = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        $cartData = $addToCartData['cart'];
        $this->assertCount(1, $cartData['items']);
        $unitPrice = $cartData['items'][0]['product']['price'];

        $paidQuantity = $requestedQuantity - $expectedFreebies;
        $expectedPriceWithVat = $this->moneyFormatterHelper->formatWithMaxFractionDigits(
            Money::create($unitPrice['priceWithVat'])->multiply($paidQuantity),
        );

        $this->assertSame($expectedFreebies, $cartData['items'][0]['freeQuantity']);
        $this->assertSame($expectedPriceWithVat, $cartData['totalPrice']['priceWithVat']);
        $this->assertSame($requestedQuantity, $cartData['items'][0]['quantity']);
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function promotionQuantitiesProvider(): array
    {
        return [
            'three items without freebies' => [3, 0],
            'four items with one freebie' => [4, 1],
            'five items with one freebie' => [5, 1],
        ];
    }
}
