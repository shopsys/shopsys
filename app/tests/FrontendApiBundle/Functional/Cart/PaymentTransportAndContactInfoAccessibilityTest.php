<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTransportAndContactInfoAccessibilityTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \App\Model\Transport\Transport
     */
    private Transport $testingTransport;

    /**
     * @var \App\Model\Payment\Payment
     */
    private Payment $testingPayment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $this->testingTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $this->testingPayment = $this->getReference(PaymentDataFixture::PAYMENT_CARD);
    }

    public function testPaymentTransportAndContactInfoAccessibilityWithAnonymousCartOnly(): void
    {
        $productQuantity = 5;
        $newlyCreatedAnonymousCart = $this->addTestingProductToNewCart($productQuantity);

        $orderStepsAccessQuery = '
            {
                orderStepsAccessibility(cartInput: {cartUuid: "' . $newlyCreatedAnonymousCart['uuid'] . '"}) {
                    canAccessContactInformation
                    canAccessTransportAndPayment
                }
            }
        ';
        $response = $this->getResponseContentForQuery($orderStepsAccessQuery);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderStepsAccessibility');

        self::AssertTrue($responseData['canAccessTransportAndPayment']);
        self::AssertFalse($responseData['canAccessContactInformation']);
    }

    public function testPaymentTransportAndContactInfoAccessibilityWithAnonymousCartTransportAndPayment(): void
    {
        $productQuantity = 2;
        $newlyCreatedAnonymousCart = $this->addTestingProductToNewCart($productQuantity);

        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1);
        $pickupPlaceIdentifier = $store->getUuid();

        $this->addTransportToCart($newlyCreatedAnonymousCart, $this->testingTransport, $pickupPlaceIdentifier);
        $this->addPaymentToCart($newlyCreatedAnonymousCart, $this->testingPayment);

        $orderStepsAccessQuery = '
            {
                orderStepsAccessibility(cartInput: {cartUuid: "' . $newlyCreatedAnonymousCart['uuid'] . '"}) {
                    canAccessContactInformation
                    canAccessTransportAndPayment
                }
            }
        ';

        $response = $this->getResponseContentForQuery($orderStepsAccessQuery);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderStepsAccessibility');

        self::AssertTrue($responseData['canAccessTransportAndPayment']);
        self::AssertTrue($responseData['canAccessContactInformation']);
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
            ]
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
            ]
        );
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
    }
}
