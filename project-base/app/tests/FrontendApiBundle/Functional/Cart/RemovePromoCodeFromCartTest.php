<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Tests\FrontendApiBundle\Test\PromoCodeAssertionTrait;

class RemovePromoCodeFromCartTest extends GraphQlTestCase
{
    use PromoCodeAssertionTrait;

    public function testRemovePromoCodeFromCart(): void
    {
        $promoCode = $this->applyValidPromoCodeToDefaultCart();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemovePromoCodeFromCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'promoCode' => $promoCode->getCode(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'RemovePromoCodeFromCart');

        self::assertCount(0, $data['promoCodes']);
    }

    public function testPromoCodeIsRemovedFromCartAfterDeletion(): void
    {
        $promoCode = $this->applyValidPromoCodeToDefaultCart();

        $this->em->remove($promoCode);
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        self::assertCount(0, $data['promoCodes']);

        // if promo code is deleted, CartWatcher cannot possibly know about it and report modification
        self::assertEmpty($data['modifications']['promoCodeModifications']['noLongerApplicablePromoCode']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function applyValidPromoCodeToDefaultCart(): PromoCode
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'promoCode' => $promoCode->getCode(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertPromoCode($promoCode, $data['promoCodes'][0]);

        return $promoCode;
    }
}
