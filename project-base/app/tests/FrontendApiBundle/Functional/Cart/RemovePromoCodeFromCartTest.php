<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemovePromoCodeFromCartTest extends GraphQlTestCase
{
    public function testRemovePromoCodeFromCart(): void
    {
        $promoCode = $this->applyValidPromoCodeToDefaultCart();

        $removeFromCartMutation = 'mutation {
            RemovePromoCodeFromCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($removeFromCartMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'RemovePromoCodeFromCart');

        self::assertNull($data['promoCode']);
    }

    public function testPromoCodeIsRemovedFromCartAfterDeletion(): void
    {
        $promoCode = $this->applyValidPromoCodeToDefaultCart();

        $this->em->remove($promoCode);
        $this->em->flush();

        $getCartQuery = '{
            cart(cartInput: {cartUuid: "' . CartDataFixture::CART_UUID . '"}) {
                promoCode
                modifications {
                    promoCodeModifications {
                        noLongerApplicablePromoCode
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        self::assertNull($data['promoCode']);

        // if promo code is deleted, CartWatcher cannot possibly know about it and report modification
        self::assertEmpty($data['modifications']['promoCodeModifications']['noLongerApplicablePromoCode']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function applyValidPromoCodeToDefaultCart(): PromoCode
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . CartDataFixture::CART_UUID . '"
                promoCode: "' . $promoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $response = $this->getResponseContentForQuery($applyPromoCodeMutation);
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');

        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        return $promoCode;
    }
}
