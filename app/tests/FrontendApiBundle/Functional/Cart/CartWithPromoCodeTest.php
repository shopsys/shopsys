<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Functional\Order\AbstractOrderTestCase;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartWithPromoCodeTest extends GraphQlTestCase
{
    public function testCartManipulationWithPromoCode(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $initCartResponse = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product1->getUuid(),
            'quantity' => 1,
        ]);

        $initCartResult = $initCartResponse['data']['AddToCart'];
        $cartUuid = $initCartResult['cart']['uuid'];

        /** @var \App\Model\Order\PromoCode\PromoCode $validPromoCode */
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, Domain::FIRST_DOMAIN_ID);

        $applyPromoCodeMutation = 'mutation {
            ApplyPromoCodeToCart(input: {
                cartUuid: "' . $cartUuid . '"
                promoCode: "' . $validPromoCode->getCode() . '"
            }) {
                uuid
                promoCode
            }
        }';

        $this->getResponseContentForQuery($applyPromoCodeMutation);

        $query = 'query{
            cart(cartInput: {
                cartUuid: "' . $cartUuid . '"
            }){
                uuid
                items{
                    uuid
                    quantity
                }
                totalPrice{
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
                totalDiscountPrice{
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
            }
        }';

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId());

        $cartData = $this->getResponseContentForQuery($query)['data']['cart'];
        $this->assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('2602.48', $vatHigh), $cartData['totalPrice']);
        $this->assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('289.26', $vatHigh), $cartData['totalDiscountPrice']);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product72 */
        $product72 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');

        $addAnotherToCartResponse = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product72->getUuid(),
            'quantity' => 1,
        ]);

        $totalPrice = AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems([
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('2602.48', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('90', $vatHigh)],
        ]);

        $totalPriceExpected = [
            'priceWithVat' => $totalPrice->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $totalPrice->getPriceWithoutVat()->getAmount(),
            'vatAmount' => $totalPrice->getVatAmount()->getAmount(),
        ];

        $totalDiscountPrice = AbstractOrderTestCase::getOrderTotalPriceByExpectedOrderItems([
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('289.26', $vatHigh)],
            ['totalPrice' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('9.92', $vatHigh)],
        ]);

        $totalDiscountPriceExpected = [
            'priceWithVat' => $totalDiscountPrice->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $totalDiscountPrice->getPriceWithoutVat()->getAmount(),
            'vatAmount' => $totalDiscountPrice->getVatAmount()->getAmount(),
        ];

        $addToCartResult = $addAnotherToCartResponse['data']['AddToCart'];
        $this->assertSame($totalPriceExpected, $addToCartResult['cart']['totalPrice']);
        $this->assertSame($totalDiscountPriceExpected, $addToCartResult['cart']['totalDiscountPrice']);
    }
}
