<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentOperationsAuthorizationTest extends GraphQlTestCase
{
    private const string MISSING_PROOF_ERROR_MESSAGE = 'You need to be logged in or provide argument \'orderUrlHash\'.';

    public function testUpdatePaymentStatusWithUuidOnlyIsDeniedForAnonymousUser(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertMissingProofError($response);
    }

    public function testPayOrderWithUuidOnlyIsDeniedForAnonymousUser(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PayOrderMutation.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertMissingProofError($response);
    }

    public function testChangePaymentInOrderWithUuidOnlyIsDeniedForAnonymousUser(): void
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

        $this->assertMissingProofError($response);
    }

    public function testOrderPaymentsWithUuidOnlyIsDeniedForAnonymousUser(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertMissingProofError($response);
    }

    public function testUpdatePaymentStatusWithMismatchedUrlHashIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $anotherOrder = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => $anotherOrder->getUrlHash()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testUpdatePaymentStatusWithUnknownUrlHashIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/UpdatePaymentStatusMutation.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => 'unknown-url-hash'],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testChangePaymentInOrderWithMismatchedUrlHashIsDeniedWithoutStateLeak(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $anotherOrder = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'orderUrlHash' => $anotherOrder->getUrlHash(),
                    'paymentUuid' => $payment->getUuid(),
                ],
            ],
        );

        $this->assertUserError($response, 'order-not-found');
        $this->assertResponseContainsNoValidationErrors($response);
    }

    private function assertMissingProofError(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame(self::MISSING_PROOF_ERROR_MESSAGE, $errors[0]['message']);
    }

    private function assertResponseContainsNoValidationErrors(array $response): void
    {
        foreach ($this->getErrorsFromResponse($response) as $error) {
            $this->assertArrayNotHasKey('validation', $error['extensions'] ?? []);
        }
    }
}
