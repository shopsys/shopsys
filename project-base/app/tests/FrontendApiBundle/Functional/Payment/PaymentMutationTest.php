<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Payment\Transaction\PaymentTransactionDataFactory;
use App\Model\Payment\Transaction\PaymentTransactionFacade;
use GoPay\Definition\Response\PaymentStatus;
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
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);

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

    public function testCheckPaymentStatusWithGoPay(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);

        $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CheckPaymentMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'CheckPaymentStatus');

        $this->assertTrue($content['isPaid']);
        $this->assertSame(2, $content['transactionCount']);
        $this->assertSame(PaymentDataFixture::PAYMENT_GOPAY, $content['paymentType']);


        $this->em->clear();
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);

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

        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame($errors[0]['extensions']['userCode'], 'order-already-paid');
    }

    public function testOrderCannotBePaidForPaymentWithTwoTransactions(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame($errors[0]['extensions']['userCode'], 'max-transaction-count-reached');
    }
}
