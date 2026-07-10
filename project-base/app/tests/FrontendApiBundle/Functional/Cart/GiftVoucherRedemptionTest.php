<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\GiftVoucherDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\Model\Product\Product;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDataFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrontendApiBundle\Component\Constraints\GiftVoucher as GiftVoucherConstraint;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode as PromoCodeConstraint;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GiftVoucherRedemptionTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private GiftVoucherDataFactory $giftVoucherDataFactory;

    /**
     * @inject
     */
    private GiftVoucherFacade $giftVoucherFacade;

    public function testGiftVoucherRedeemedElsewhereIsRemovedFromCartWithModification(): void
    {
        $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        $giftVoucher = $this->getReference(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED, GiftVoucher::class);
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1', Order::class);
        $giftVoucher->markAsRedeemed($order, new DateTimeImmutable());
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GiftVoucherCartModifications.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        self::assertSame([], $data['giftVouchers']);
        self::assertSame(
            [GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE],
            $data['modifications']['giftVoucherModifications']['noLongerApplicableGiftVouchers'],
        );
    }

    public function testApplyGiftVoucherToCartReducesRemainingAmountToPay(): void
    {
        $data = $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertCount(1, $data['giftVouchers']);
        self::assertSame(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE, $data['giftVouchers'][0]['code']);

        $giftVoucher = $this->getReference(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED, GiftVoucher::class);
        $totalPriceWithVat = Money::create($data['totalPrice']['priceWithVat']);
        self::assertTrue(
            $totalPriceWithVat->isGreaterThan($giftVoucher->getValueWithVat()),
            'Demo cart must exceed the voucher value for this test to be meaningful.',
        );

        $expectedRemainingAmountToPay = $totalPriceWithVat->subtract($giftVoucher->getValueWithVat());

        self::assertTrue($expectedRemainingAmountToPay->equals(Money::create($data['remainingAmountToPay'])));
    }

    public function testEnteredGiftVoucherCodeIsNormalized(): void
    {
        $data = $this->applyGiftVoucherToCartAndGetResponseData(' happy-day 2345 ');

        self::assertCount(1, $data['giftVouchers']);
        self::assertSame(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE, $data['giftVouchers'][0]['code']);
    }

    public function testGiftVoucherCanBeCombinedWithPromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);
        $this->applyPromoCodeToCart($promoCode->getCode());

        $data = $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        self::assertCount(1, $data['promoCodes']);
        self::assertCount(1, $data['giftVouchers']);
    }

    public function testApplyRedeemedGiftVoucherReturnsError(): void
    {
        $this->assertApplyCodeValidationError(
            GiftVoucherDataFixture::GIFT_VOUCHER_REDEEMED_CODE,
            GiftVoucherConstraint::GIFT_VOUCHER_NOT_REDEEMABLE_ERROR,
        );
    }

    public function testApplyCancelledGiftVoucherReturnsError(): void
    {
        $this->assertApplyCodeValidationError(
            GiftVoucherDataFixture::GIFT_VOUCHER_CANCELLED_CODE,
            GiftVoucherConstraint::GIFT_VOUCHER_NOT_REDEEMABLE_ERROR,
        );
    }

    public function testApplyExpiredGiftVoucherReturnsError(): void
    {
        $this->assertApplyCodeValidationError(
            GiftVoucherDataFixture::GIFT_VOUCHER_EXPIRED_CODE,
            GiftVoucherConstraint::NO_LONGER_VALID_ERROR,
        );
    }

    public function testApplyGiftVoucherFromAnotherDomainReturnsError(): void
    {
        $this->assertApplyCodeValidationError(
            GiftVoucherDataFixture::GIFT_VOUCHER_SECOND_DOMAIN_CODE,
            GiftVoucherConstraint::INVALID_ERROR,
        );
    }

    public function testApplyAlreadyAppliedGiftVoucherReturnsError(): void
    {
        $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        $this->assertApplyCodeValidationError(
            GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE,
            GiftVoucherConstraint::ALREADY_APPLIED_GIFT_VOUCHER_ERROR,
        );
    }

    public function testPromoCodeIsNotApplicableToCartWithOnlyGiftVoucherProducts(): void
    {
        $voucherProduct = $this->getReference(ProductDataFixture::PRODUCT_ELECTRONIC_GIFT_VOUCHER_1000, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => null,
            'productUuid' => $voucherProduct->getUuid(),
            'quantity' => 1,
        ]);
        $voucherOnlyCartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $voucherOnlyCartUuid,
            'promoCode' => $promoCode->getCode(),
        ]);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals(
            PromoCodeConstraint::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR,
            $violations['input.promoCode'][0]['code'],
        );
    }

    public function testPromoCodeDiscountIsNotAppliedToGiftVoucherProducts(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);
        $this->applyPromoCodeToCart($promoCode->getCode());

        $totalDiscountPriceBeforeAddingVoucherProduct = $this->getCartPrices()['totalDiscountPrice']['priceWithVat'];

        $this->addProductToFixtureCart($this->getReference(ProductDataFixture::PRODUCT_ELECTRONIC_GIFT_VOUCHER_1000, Product::class));

        $totalDiscountPriceAfterAddingVoucherProduct = $this->getCartPrices()['totalDiscountPrice']['priceWithVat'];

        self::assertSame($totalDiscountPriceBeforeAddingVoucherProduct, $totalDiscountPriceAfterAddingVoucherProduct);
    }

    public function testGiftVouchersExceedPayableAmountWhenVoucherValueIsNotCoveredByNonGiftVoucherPartOfCart(): void
    {
        $totalPriceWithoutVoucherProduct = Money::create($this->getCartPrices()['totalPrice']['priceWithVat']);

        $this->addProductToFixtureCart($this->getReference(ProductDataFixture::PRODUCT_ELECTRONIC_GIFT_VOUCHER_1000, Product::class));

        $totalPriceWithVat = Money::create($this->getCartPrices()['totalPrice']['priceWithVat']);
        $giftVoucherProductItemsPrice = $totalPriceWithVat->subtract($totalPriceWithoutVoucherProduct);
        $payableAmount = $totalPriceWithVat->subtract($giftVoucherProductItemsPrice);

        $giftVoucher = $this->createUnredeemedGiftVoucherWithValue($payableAmount->add(Money::create(1)));
        $this->applyGiftVoucherToCartAndGetResponseData($giftVoucher->getCode());

        $cartPrices = $this->getCartPrices();

        $expectedRemainingAmountToPay = $totalPriceWithVat->subtract($giftVoucher->getValueWithVat());

        if ($expectedRemainingAmountToPay->isNegative()) {
            $expectedRemainingAmountToPay = Money::zero();
        }

        self::assertTrue($giftVoucherProductItemsPrice->isPositive());
        self::assertTrue($giftVoucher->getValueWithVat()->isGreaterThan($payableAmount));
        self::assertTrue($cartPrices['giftVouchersExceedPayableAmount']);
        self::assertTrue($expectedRemainingAmountToPay->equals(Money::create($cartPrices['remainingAmountToPay'])));
    }

    public function testGiftVouchersDoNotExceedPayableAmountWhenVoucherValueIsCoveredByNonGiftVoucherPartOfCart(): void
    {
        $data = $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_FULL_PAYMENT_CODE);

        self::assertFalse($data['giftVouchersExceedPayableAmount']);
        self::assertTrue(
            Money::create($data['totalPrice']['priceWithVat'])
                ->subtract(Money::create(GiftVoucherDataFixture::GIFT_VOUCHER_FULL_PAYMENT_VALUE))
                ->equals(Money::create($data['remainingAmountToPay'])),
        );
    }

    public function testRemoveGiftVoucherFromCart(): void
    {
        $this->applyGiftVoucherToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveGiftVoucherFromCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'giftVoucherCode' => GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'RemoveCodeFromCart');

        self::assertCount(0, $data['giftVouchers']);
        self::assertTrue(
            Money::create($data['totalPrice']['priceWithVat'])->equals(Money::create($data['remainingAmountToPay'])),
        );
    }

    private function assertApplyCodeValidationError(string $enteredCode, string $expectedErrorCode): void
    {
        $response = $this->applyGiftVoucherToCart($enteredCode);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.giftVoucherCode', $violations);
        self::assertEquals($expectedErrorCode, $violations['input.giftVoucherCode'][0]['code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function applyGiftVoucherToCartAndGetResponseData(string $enteredCode): array
    {
        $response = $this->applyGiftVoucherToCart($enteredCode);

        return $this->getResponseDataForGraphQlType($response, 'ApplyCodeToCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function applyGiftVoucherToCart(string $enteredCode): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyGiftVoucherToCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'giftVoucherCode' => $enteredCode,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function applyPromoCodeToCart(string $promoCodeCode): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'promoCode' => $promoCodeCode,
        ]);
    }

    private function addProductToFixtureCart(Product $product): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'AddToCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function getCartPrices(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CartPricesQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'cart');
    }

    private function createUnredeemedGiftVoucherWithValue(Money $value): GiftVoucher
    {
        $giftVoucherData = $this->giftVoucherDataFactory->createForDomainId($this->domain->getId());
        $giftVoucherData->valueWithVat = $value;

        return $this->giftVoucherFacade->create($giftVoucherData);
    }
}
