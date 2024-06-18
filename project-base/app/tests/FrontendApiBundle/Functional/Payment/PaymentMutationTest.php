<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PaymentTransactionFacade $paymentTransactionFacade;

    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    public function testPayOrderWithGoPay(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_ONE_TRANSACTION, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'PayOrder');

        $this->assertArrayHasKey('goPayCreatePaymentSetup', $content);
        $this->assertArrayHasKey('gatewayUrl', $content['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('goPayId', $content['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('embedJs', $content['goPayCreatePaymentSetup']);
        $this->assertSame('https://example.com?supertoken=xyz123456', $content['goPayCreatePaymentSetup']['gatewayUrl']);
        $this->assertSame('987654321', $content['goPayCreatePaymentSetup']['goPayId']);
    }

    public function testUpdatePaymentStatusWithGoPay(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_ONE_TRANSACTION, Order::class);

        $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'UpdatePaymentStatus');

        $this->assertTrue($content['isPaid']);
        $this->assertSame(2, $content['paymentTransactionsCount']);
        $this->assertSame(Payment::TYPE_GOPAY, $content['payment']['type']);


        $this->em->clear();
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_ONE_TRANSACTION, Order::class);

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $this->assertSame('PAID', $paymentTransaction->getExternalPaymentStatus());
        }
    }

    public function testOrderCannotBePaidForAlreadyPaidOrder(): void
    {
        // set transaction as paid
        $paymentTransaction = $this->paymentTransactionFacade->getById(1);
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
        $paymentTransactionData->externalPaymentStatus = PaymentStatus::PAID;
        $this->paymentTransactionFacade->edit(1, $paymentTransactionData);

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_ONE_TRANSACTION, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('order-already-paid', $errors[0]['extensions']['userCode']);
    }

    public function testOrderCannotBePaidForPaymentWithTwoTransactions(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_TWO_TRANSACTIONS, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('max-transaction-count-reached', $errors[0]['extensions']['userCode']);
    }
}
