<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\GiftVoucherDataFixture;
use Shopsys\FrontendApiBundle\Component\Constraints\GiftVoucher as GiftVoucherConstraint;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode as PromoCodeConstraint;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ApplyCodeToCartTest extends GraphQlTestCase
{
    private const string VALID_PROMO_CODE = 'test';

    public function testGiftVoucherCodeIsAppliedAsGiftVoucher(): void
    {
        $data = $this->applyCodeToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        self::assertSame(
            [GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE],
            array_column($data['giftVouchers'], 'code'),
        );
        self::assertSame([], $data['promoCodes']);
    }

    public function testPromoCodeIsAppliedAsPromoCode(): void
    {
        $data = $this->applyCodeToCartAndGetResponseData(self::VALID_PROMO_CODE);

        self::assertSame([self::VALID_PROMO_CODE], array_column($data['promoCodes'], 'code'));
        self::assertSame([], $data['giftVouchers']);
    }

    public function testNotNormalizedGiftVoucherCodeIsApplied(): void
    {
        $data = $this->applyCodeToCartAndGetResponseData(' happy-day 2345 ');

        self::assertSame(
            [GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE],
            array_column($data['giftVouchers'], 'code'),
        );
    }

    public function testUnknownCodeIsReportedAsInvalidPromoCode(): void
    {
        $response = $this->applyCodeToCart('unknown-code-123');

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertSame(PromoCodeConstraint::INVALID_ERROR, $violations['input.promoCode'][0]['code']);
    }

    public function testGiftVoucherValidationErrorIsReported(): void
    {
        $this->applyCodeToCartAndGetResponseData(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        $response = $this->applyCodeToCart(GiftVoucherDataFixture::GIFT_VOUCHER_UNREDEEMED_CODE);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.giftVoucherCode', $violations);
        self::assertSame(
            GiftVoucherConstraint::ALREADY_APPLIED_GIFT_VOUCHER_ERROR,
            $violations['input.giftVoucherCode'][0]['code'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function applyCodeToCartAndGetResponseData(string $code): array
    {
        return $this->getResponseDataForGraphQlType($this->applyCodeToCart($code), 'ApplyCodeToCart');
    }

    /**
     * @return array<string, mixed>
     */
    private function applyCodeToCart(string $code): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyCodeToCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'code' => $code,
        ]);
    }
}
