<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentMutationTest extends GraphQlTestCase
{
    public function testPayOrderWithGoPay(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_CZ);

        $mutation = $this->getPayOrderMutation($order->getUuid());

        $content = $this->getResponseContentForQuery($mutation);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('PayOrder', $content['data']);
        $this->assertArrayHasKey('goPayCreatePaymentSetup', $content['data']['PayOrder']);
        $this->assertArrayHasKey('gatewayUrl', $content['data']['PayOrder']['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('goPayId', $content['data']['PayOrder']['goPayCreatePaymentSetup']);
        $this->assertArrayHasKey('embedJs', $content['data']['PayOrder']['goPayCreatePaymentSetup']);
        $this->assertSame('https://example.com?supertoken=xyz123456', $content['data']['PayOrder']['goPayCreatePaymentSetup']['gatewayUrl']);
        $this->assertSame('987654321', $content['data']['PayOrder']['goPayCreatePaymentSetup']['goPayId']);
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    private function getPayOrderMutation(string $orderUuid): string
    {
        return '
            mutation {
                PayOrder(orderUuid: "' . $orderUuid . '") {
                    goPayCreatePaymentSetup {
                        gatewayUrl
                        goPayId
                        embedJs
                    }
                }
            }
        ';
    }

    public function testCheckPaymentStatusWithGoPay(): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_CZ);

        $mutation = $this->getPayOrderMutation($order->getUuid());
        $this->getResponseContentForQuery($mutation);

        $mutation = $this->getCheckPaymentStatusMutation($order->getUuid());
        $content = $this->getResponseContentForQuery($mutation);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('CheckPaymentStatus', $content['data']);
        $this->assertTrue($content['data']['CheckPaymentStatus']);

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $this->assertSame('PAID', $paymentTransaction->getExternalPaymentStatus());
        }
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    private function getCheckPaymentStatusMutation(string $orderUuid): string
    {
        return '
            mutation {
                CheckPaymentStatus(orderUuid: "' . $orderUuid . '")
            }
        ';
    }
}
