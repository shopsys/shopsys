<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentPriceInOrderContextAuthorizationTest extends GraphQlTestCase
{
    private const string MISSING_PROOF_ERROR_MESSAGE = 'You need to be logged in or provide argument \'orderUrlHash\'.';

    public function testPaymentPricesInOrderContextWithUuidOnlyAreDeniedForAnonymousUser(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/PaymentsPricesWithOrderUuidVariableQuery.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame(self::MISSING_PROOF_ERROR_MESSAGE, $errors[0]['message']);
    }
}
