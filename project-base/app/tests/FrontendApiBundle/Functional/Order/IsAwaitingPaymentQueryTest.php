<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class IsAwaitingPaymentQueryTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PaymentTransactionFacade $paymentTransactionFacade;

    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    /**
     * @inject
     */
    private OrderCancellationTestHelper $orderCancellationTestHelper;

    public function testOrderWithUnpaidExternalPaymentIsAwaitingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $this->assertTrue($this->getIsAwaitingPayment($order));
    }

    public function testCancelledOrderIsNotAwaitingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->orderCancellationTestHelper->cancelOrder($order);

        $this->assertFalse($this->getIsAwaitingPayment($order));
    }

    public function testPaidOrderIsNotAwaitingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->setLastPaymentTransactionStatus($order, PaymentStatus::PAID);

        $this->assertFalse($this->getIsAwaitingPayment($order));
    }

    public function testOrderWithPaymentInProcessIsNotAwaitingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->setLastPaymentTransactionStatus($order, PaymentStatus::PAYMENT_METHOD_CHOSEN);

        $this->assertFalse($this->getIsAwaitingPayment($order));
    }

    public function testOrderWithInternalPaymentIsNotAwaitingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 3, Order::class);

        $this->assertFalse($this->getIsAwaitingPayment($order));
    }

    private function getIsAwaitingPayment(Order $order): bool
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/IsAwaitingPaymentQuery.graphql',
            [
                'urlHash' => $order->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'order');

        return $data['isAwaitingPayment'];
    }

    private function setLastPaymentTransactionStatus(Order $order, string $externalPaymentStatus): void
    {
        $paymentTransaction = $order->getLastTransaction();
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
        $paymentTransactionData->externalPaymentStatus = $externalPaymentStatus;

        $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
    }
}
