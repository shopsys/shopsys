<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartDiscountBreakdownTest extends GraphQlTestCase
{
    public function testCartDiscountBreakdownFieldsArePositive(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $validPromoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::VALID_PROMO_CODE,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );

        $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $validPromoCode->getCode(),
        ]);

        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        // All discount prices should be positive (representing savings amount)
        $this->assertGreaterThanOrEqual(
            0,
            Money::create($cartData['totalProductPriceAdjustmentsDiscount']['priceWithVat'])->getAmount(),
            'totalProductPriceAdjustmentsDiscount should be positive or zero',
        );

        $this->assertGreaterThan(
            0,
            Money::create($cartData['promoCodes'][0]['discountPrice']['priceWithVat'])->getAmount(),
            'promo code discount price should be positive when promo code is applied',
        );

        $this->assertGreaterThan(
            0,
            Money::create($cartData['totalDiscountPrice']['priceWithVat'])->getAmount(),
            'totalDiscountPrice should be positive when discounts are applied',
        );
    }

    public function testTotalDiscountPriceEqualsProductAndPromoCodeDiscounts(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $validPromoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::VALID_PROMO_CODE,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );

        $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $validPromoCode->getCode(),
        ]);

        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        $productDiscount = Money::create($cartData['totalProductPriceAdjustmentsDiscount']['priceWithVat']);
        $promoCodeDiscount = Money::create($cartData['promoCodes'][0]['discountPrice']['priceWithVat']);
        $totalDiscount = Money::create($cartData['totalDiscountPrice']['priceWithVat']);

        $calculatedTotal = $productDiscount->add($promoCodeDiscount);

        $this->assertTrue(
            $totalDiscount->equals($calculatedTotal),
            sprintf(
                'totalDiscountPrice (%s) should equal sum of totalProductPriceAdjustmentsDiscount (%s) + promo code discount price (%s)',
                $totalDiscount->getAmount(),
                $productDiscount->getAmount(),
                $promoCodeDiscount->getAmount(),
            ),
        );
    }

    public function testTotalItemsPriceBeforeDiscountIsGreaterThanTotalItemsPrice(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $validPromoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::VALID_PROMO_CODE,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );

        $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $validPromoCode->getCode(),
        ]);

        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        $priceBeforeDiscount = Money::create($cartData['totalItemsPriceBeforeDiscount']['priceWithVat']);
        $priceAfterDiscount = Money::create($cartData['totalItemsPrice']['priceWithVat']);

        $this->assertTrue(
            $priceBeforeDiscount->isGreaterThan($priceAfterDiscount),
            'totalItemsPriceBeforeDiscount should be greater than totalItemsPrice when discounts are applied',
        );
    }

    public function testTotalItemsPriceBeforeDiscountMinusTotalDiscountEqualsTotalItemsPrice(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $validPromoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::VALID_PROMO_CODE,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );

        $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $validPromoCode->getCode(),
        ]);

        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        $priceBeforeDiscount = Money::create($cartData['totalItemsPriceBeforeDiscount']['priceWithVat']);
        $totalDiscount = Money::create($cartData['totalDiscountPrice']['priceWithVat']);
        $finalPrice = Money::create($cartData['totalItemsPrice']['priceWithVat']);

        $calculatedFinalPrice = $priceBeforeDiscount->subtract($totalDiscount);

        $this->assertTrue(
            $finalPrice->equals($calculatedFinalPrice),
            sprintf(
                'totalItemsPrice (%s) should equal totalItemsPriceBeforeDiscount (%s) - totalDiscountPrice (%s)',
                $finalPrice->getAmount(),
                $priceBeforeDiscount->getAmount(),
                $totalDiscount->getAmount(),
            ),
        );
    }

    public function testCartWithoutPromoCodeHasZeroPromoCodeDiscount(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];
        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        $this->assertEmpty(
            $cartData['promoCodes'],
            'no promo code should be returned when no promo code is applied',
        );
    }

    public function testCartWithProductDiscountHasNonNegativeProductDiscount(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];
        $cartData = $this->getCartWithDiscountBreakdown($cartUuid);

        $productDiscount = Money::create($cartData['totalProductPriceAdjustmentsDiscount']['priceWithVat']);

        // Product discount should never be negative
        $this->assertGreaterThanOrEqual(
            0,
            $productDiscount->getAmount(),
            'totalProductPriceAdjustmentsDiscount should be non-negative',
        );
    }

    /**
     * @param string $cartUuid
     * @return array<string, mixed>
     */
    private function getCartWithDiscountBreakdown(string $cartUuid): array
    {
        $result = $this->getResponseContentForGql(__DIR__ . '/graphql/CartWithDiscountBreakdown.graphql', [
            'cartUuid' => $cartUuid,
        ]);

        return $this->getResponseDataForGraphQlType($result, 'cart');
    }
}
