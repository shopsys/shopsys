<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class PaymentOperationsAsAuthenticatedOwnerTest extends GraphQlWithLoginTestCase
{
    public function testPayOrderAndUpdatePaymentStatusByUuidForOwnOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $payOrderResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $payOrderContent = $this->getResponseDataForGraphQlType($payOrderResponse, 'PayOrder');

        $this->assertArrayHasKey('goPayCreatePaymentSetup', $payOrderContent);

        $updatePaymentStatusResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $updatePaymentStatusContent = $this->getResponseDataForGraphQlType($updatePaymentStatusResponse, 'UpdatePaymentStatus');

        $this->assertTrue($updatePaymentStatusContent['isPaid']);
        $this->assertSame($order->getNumber(), $updatePaymentStatusContent['orderNumber']);
    }

    public function testOrderPaymentsByUuidForOwnOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'orderPayments');

        $this->assertNotNull($content['currentPayment']);
        $this->assertNotEmpty($content['availablePayments']);
    }

    public function testChangePaymentInOrderByUuidForOwnOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
                ],
            ],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInOrder');

        $this->assertSame($order->getUuid(), $content['uuid']);
    }
}
