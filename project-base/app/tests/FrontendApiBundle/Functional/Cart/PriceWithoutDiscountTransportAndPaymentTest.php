<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

class PriceWithoutDiscountTransportAndPaymentTest extends GraphQlTestCase
{
    use PromoCodeAssertionTrait;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    public function testTotalPriceWithoutDiscountTransportAndPayment(): void
    {
        $testingProductVoucher = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $voucherQuantity = 3;
        $newlyCreatedCart = $this->addTestingProductToCart($testingProductVoucher, $voucherQuantity);

        $testingProductHelloKitty = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $helloKittyQuantity = 5;
        $this->addTestingProductToCart($testingProductHelloKitty, $helloKittyQuantity, $newlyCreatedCart['uuid']);

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, PromoCode::class);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);
        self::assertNotNull($cart);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql',
            [
                'cartUuid' => $newlyCreatedCart['uuid'],
                'promoCode' => $promoCode->getCode(),
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
        self::assertPromoCode($promoCode, $data['promoCode']);

        $testingTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $pickupPlaceIdentifier = $store->getUuid();
        $this->addTransportToCart($newlyCreatedCart, $testingTransport, $pickupPlaceIdentifier);

        $testingPayment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->addPaymentToCart($newlyCreatedCart, $testingPayment);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/TotalPriceWithoutDiscountPaymentAndTransportQuery.graphql',
            [
                'cartUuid' => $newlyCreatedCart['uuid'],
            ],
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');

        $expectedPriceWithVat = $this->priceConverter->convertPriceWithVatToDomainDefaultCurrencyPrice(Money::create('17858'), $this->getReference(CurrencyDataFixture::CURRENCY_CZK), Domain::FIRST_DOMAIN_ID);

        self::assertThat(Money::create($responseData['totalPriceWithoutDiscountTransportAndPayment']['priceWithVat']), new IsMoneyEqual($expectedPriceWithVat));
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $productQuantity
     * @param string|null $cartUuid
     * @return array
     */
    private function addTestingProductToCart(Product $product, int $productQuantity, ?string $cartUuid = null): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $product->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
    }

    /**
     * @param array $cart
     * @param \App\Model\Transport\Transport $transport
     * @param string $pickupPlaceIdentifier
     * @return array
     */
    private function addTransportToCart(array $cart, Transport $transport, string $pickupPlaceIdentifier): array
    {
        return $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql',
            [
                'cartUuid' => $cart['uuid'],
                'transportUuid' => $transport->getUuid(),
                'pickupPlaceIdentifier' => $pickupPlaceIdentifier,
            ],
        );
    }

    /**
     * @param array $cart
     * @param \App\Model\Payment\Payment $payment
     * @return array
     */
    private function addPaymentToCart(array $cart, Payment $payment): array
    {
        return $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql',
            [
                'cartUuid' => $cart['uuid'],
                'paymentUuid' => $payment->getUuid(),
            ],
        );
    }
}
