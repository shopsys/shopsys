<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTotalItemsPriceTest extends GraphQlTestCase
{
    public function testCartTotalItemsPriceDoesNotIncludeTransportAndPaymentPrice(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, 1);

        $cartUuid = $this->getResponseDataForGraphQlType($this->addHelloKittyToNewCart(), 'AddToCart')['cart']['uuid'];
        $this->addPaymentCardToCart($cartUuid);
        $this->addTransportPplToCart($cartUuid);

        $response = $this->getCartResponse($cartUuid);
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');
        $expectedPrice = $this->getSerializedPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh);

        $this->assertSame($expectedPrice, $responseData['totalItemsPrice']);
    }

    /**
     * @return array<string, mixed>
     */
    private function addHelloKittyToNewCart(): array
    {
        /** @var \App\Model\Product\Product $helloKittyProduct */
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $helloKittyProduct->getUuid(),
            'quantity' => 1,
        ]);
    }

    /**
     * @param string $cartUuid
     */
    private function addPaymentCardToCart(string $cartUuid): void
    {
        /** @var \App\Model\Payment\Payment $paymentCard */
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    paymentUuid: "' . $paymentCard->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changePaymentInCartMutation);
    }

    /**
     * @param string $cartUuid
     */
    private function addTransportPplToCart(string $cartUuid): void
    {
        /** @var \App\Model\Transport\Transport $transportPpl */
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . $cartUuid . '"
                    paymentUuid: "' . $transportPpl->getUuid() . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }

    /**
     * @param string $cartUuid
     * @return array
     */
    private function getCartResponse(string $cartUuid): array
    {
        $getCartQuery = '
            query {
                cart(cartInput:{
                    cartUuid: "' . $cartUuid . '"
                }) {
                    totalItemsPrice {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                }
            }
        ';

        return $this->getResponseContentForQuery($getCartQuery);
    }
}
