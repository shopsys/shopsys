<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class WithdrawalDeadlineQueryTest extends GraphQlTestCase
{
    public function testwithdrawalDeadlineIsNotNullForDeliveredOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/WithdrawalDeadlineQuery.graphql',
            [
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $data = $response['data']['withdrawalDeadline'];

        $this->assertNotNull($data);
    }

    public function testWithdrawalDeadlineIsNullForNotDeliveredOrder(): void
    {
        $cancelledOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_CANCELLED, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/WithdrawalDeadlineQuery.graphql',
            [
                'orderUrlHash' => $cancelledOrder->getUrlHash(),
            ],
        );

        $data = $response['data']['withdrawalDeadline'];

        $this->assertNull($data);
    }

    public function testWithdrawalDeadlineForNonExistentOrder(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/WithdrawalDeadlineQuery.graphql',
            [
                'orderUrlHash' => 'non-existent-hash-12345',
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertArrayHasKey(0, $errors);
        $this->assertSame('order-not-found', $errors[0]['extensions']['userCode']);
    }
}
