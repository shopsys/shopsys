<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use GoPay\Definition\Response\PaymentStatus;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFactory;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPageStatusEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrderSentPageContentTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private OrderContentPageFacade $orderContentPageFacade;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    /**
     * @inject
     */
    private PaymentTransactionFactory $paymentTransactionFactory;

    public function testGetOrderSentPageContent(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);

        $order = $this->createOrder($product, $transport, $transport->getPayments()[0]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $this->assertEquals(
            $this->orderContentPageFacade->getOrderSentPageContent($order),
            $response['data']['orderSentPageContent'],
        );
    }

    public function testGetPaymentPageContents(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_CARD, Payment::class);

        $order = $this->createOrder($product, $transport, $payment);
        $orderUuid = $order->getUuid();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $errors = $this->getErrorsFromResponse($response);

        $this->assertEquals(
            'order-sent-page-not-available',
            $errors[0]['extensions']['userCode'],
        );

        $order = $this->orderFacade->getByUuid($orderUuid);
        $order->setOrderPaymentStatusPageValidFromNow();
        $this->em->flush();

        // simulate payment failure
        $order = $this->orderFacade->getByUuid($orderUuid);
        $paymentTransaction = $this->createPaymentTransaction($order, PaymentStatus::CANCELED);
        $order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderPaymentPageContent');

        $this->assertSame(strtoupper(PaymentContentPageStatusEnum::STATUS_FAILED), $responseData['status']);
        $this->assertSame($this->orderContentPageFacade->getPaymentFailedPageContent($order), $responseData['content']);

        // simulate payment in process
        $order = $this->orderFacade->getByUuid($orderUuid);
        $paymentTransaction = $this->createPaymentTransaction($order, PaymentStatus::PAYMENT_METHOD_CHOSEN);
        $order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderPaymentPageContent');

        $this->assertSame(strtoupper(PaymentContentPageStatusEnum::STATUS_IN_PROCESS), $responseData['status']);
        $this->assertSame($this->orderContentPageFacade->getPaymentInProcessPageContent($order), $responseData['content']);

        // simulate paid payment
        $order = $this->orderFacade->getByUuid($orderUuid);
        $paymentTransaction = $this->createPaymentTransaction($order, PaymentStatus::PAID);
        $order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderPaymentPageContent');

        $this->assertSame(strtoupper(PaymentContentPageStatusEnum::STATUS_SUCCESSFUL), $responseData['status']);
        $this->assertSame($this->orderContentPageFacade->getPaymentSuccessfulPageContent($order), $responseData['content']);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $externalStatus
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction
     */
    public function createPaymentTransaction(Order $order, string $externalStatus): PaymentTransaction
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();
        $paymentTransactionData->externalPaymentIdentifier = (string)random_int(11111, 99999);
        $paymentTransactionData->externalPaymentStatus = $externalStatus;

        $paymentTransaction = $this->paymentTransactionFactory->create($paymentTransactionData);
        $this->em->persist($paymentTransaction);

        return $paymentTransaction;
    }

    public function testGetOrderSentPageContentForNonExistingOrder(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => '4c0e44a5-74fc-4df3-b868-c4900b36adbf',
        ]);

        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals("Order with UUID '4c0e44a5-74fc-4df3-b868-c4900b36adbf' not found.", $errors[0]['message']);
    }

    public function testMakeOrderSentPageAccessibleByHash(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $orderUuid = $order->getUuid();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $orderUuid,
        ]);

        $errors = $this->getErrorsFromResponse($response);

        $this->assertEquals(
            'order-sent-page-not-available',
            $errors[0]['extensions']['userCode'],
        );

        $validityHash = Uuid::uuid4();

        $this->getResponseContentForGql(__DIR__ . '/graphql/SetOrderPaymentStatusPageValidityHashMutation.graphql', [
            'orderUuid' => $orderUuid,
            'orderPaymentStatusPageValidityHash' => $validityHash,
        ]);

        $this->getResponseContentForGql(
            __DIR__ . '/../Payment/graphql/UpdatePaymentStatusMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderPaymentStatusPageValidityHash' => $validityHash,
            ],
        );

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentPageContentQuery.graphql', [
            'orderUuid' => $orderUuid,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderPaymentPageContent');

        $this->assertSame(strtoupper(PaymentContentPageStatusEnum::STATUS_SUCCESSFUL), $responseData['status']);
        $this->assertSame($this->orderContentPageFacade->getPaymentSuccessfulPageContent($order), $responseData['content']);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Order\Order
     */
    private function createOrder(Product $product, Transport $transport, Payment $payment): Order
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'transportUuid' => $transport->getUuid(),
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'paymentUuid' => $payment->getUuid(),
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'isDeliveryAddressDifferentFromBilling' => false,
        ]);

        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];

        return $this->orderFacade->getByUuid($orderUuid);
    }
}
