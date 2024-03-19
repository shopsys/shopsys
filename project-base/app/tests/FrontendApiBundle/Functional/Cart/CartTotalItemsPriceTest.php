<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartTotalItemsPriceTest extends GraphQlTestCase
{
    public function testCartTotalItemsPriceDoesNotIncludeTransportAndPaymentPrice(): void
    {
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, 1, Vat::class);

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
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

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
        $paymentCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
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
        $transportPpl = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
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
