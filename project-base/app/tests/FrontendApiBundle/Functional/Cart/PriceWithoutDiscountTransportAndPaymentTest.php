<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PriceWithoutDiscountTransportAndPaymentTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private CartFacade $cartFacade;

    public function testTotalPriceWithoutDiscountTransportAndPayment(): void
    {
        $testingProductVoucher = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        $voucherQuantity = 3;
        $newlyCreatedCart = $this->addTestingProductToCart($testingProductVoucher, $voucherQuantity);

        $testingProductBook = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '26', Product::class);
        $bookQuantity = 5;
        $this->addTestingProductToCart($testingProductBook, $bookQuantity, $newlyCreatedCart['uuid']);

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
        self::assertEquals($promoCode->getCode(), $data['promoCodes'][0]['code']);

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

        $expectedPriceWithVat = '23.470000';

        self::assertEquals($expectedPriceWithVat, $responseData['totalPriceWithoutDiscountTransportAndPayment']['priceWithVat']);
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
