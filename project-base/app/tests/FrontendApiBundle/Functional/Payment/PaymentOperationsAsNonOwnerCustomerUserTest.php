<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class PaymentOperationsAsNonOwnerCustomerUserTest extends GraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const string DEFAULT_USER_PASSWORD = 'no-reply.3';

    public function testPayOrderByUuidForForeignOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testUpdatePaymentStatusByUuidForForeignOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testChangePaymentInOrderByUuidForForeignOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'paymentUuid' => $payment->getUuid(),
                ],
            ],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testOrderPaymentsByUuidForForeignOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testUpdatePaymentStatusWithValidUrlHashForForeignOrderSucceeds(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => $order->getUrlHash()],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'UpdatePaymentStatus');

        $this->assertSame($order->getNumber(), $content['orderNumber']);
    }
}
