<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\ReturnHash\PaymentReturnHashFacade;
use Tests\FrontendApiBundle\Functional\Order\OrderPaidTestHelper;
use Tests\FrontendApiBundle\Functional\Payment\GoPay\GoPayClient;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Uri\Rfc3986\Uri;

class PaymentMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private OrderPaidTestHelper $orderPaidTestHelper;

    /**
     * @inject
     */
    private PaymentReturnHashFacade $paymentReturnHashFacade;

    public function testPayOrderWithGoPay(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'PayOrder');

        $this->assertArrayHasKey('goPayCreatePaymentSetup', $content);
        $this->assertArrayHasKey('gatewayUrl', $content['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('goPayId', $content['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('embedJs', $content['goPayCreatePaymentSetup']);
        $this->assertSame('https://example.com?supertoken=xyz123456', $content['goPayCreatePaymentSetup']['gatewayUrl']);
        $this->assertSame('987654321', $content['goPayCreatePaymentSetup']['goPayId']);

        $this->assertNotNull(GoPayClient::$lastRawPayment);
        $this->assertArrayHasKey('callback', GoPayClient::$lastRawPayment, 'Mocked GoPay client did not set payment data properly - missing callback key');
        $this->assertArrayHasKey('return_url', GoPayClient::$lastRawPayment['callback'], 'Mocked GoPay client did not set payment data properly - missing callback->return_url key');

        $returnUrl = GoPayClient::$lastRawPayment['callback']['return_url'];
        $queryParams = new Uri($returnUrl)->getQuery();
        parse_str($queryParams, $parsedQueryParams);

        $this->assertArrayHasKey('returnHash', $parsedQueryParams);

        $returnHash = $parsedQueryParams['returnHash'];
        $paymentReturnHash = $this->paymentReturnHashFacade->findValidByHash($returnHash);

        $this->assertNotNull($paymentReturnHash);
        $this->assertSame($order->getUrlHash(), $paymentReturnHash->getOrder()->getUrlHash());
    }

    public function testUpdatePaymentStatusWithGoPay(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );
        $content = $this->getResponseDataForGraphQlType($response, 'UpdatePaymentStatus');

        $this->assertTrue($content['isPaid']);
        $this->assertSame($order->getNumber(), $content['orderNumber']);
        $this->assertSame(
            $order->getPayment()->getName($this->getLocaleForFirstDomain()),
            $content['paymentName'],
        );

        $this->em->clear();
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $this->assertSame('PAID', $paymentTransaction->getExternalPaymentStatus());
        }
    }

    public function testOrderCannotBePaidForAlreadyPaidOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->orderPaidTestHelper->markOrderAsPaidByPaymentTransactions($order);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $this->assertUserError($response, 'order-already-paid');
    }

    public function testOrderCannotBePaidForPaymentWithTwoTransactions(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $this->assertUserError($response, 'max-transaction-count-reached');
    }
}
