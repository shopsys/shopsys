<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderWithdrawalRequestValidationTest extends GraphQlTestCase
{
    public function testWithdrawalRequestForCancelledOrderFails(): void
    {
        $cancelledOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_CANCELLED, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            [
                'orderUrlHash' => $cancelledOrder->getUrlHash(),
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
            ],
        );

        $this->assertUserError($response, 'order-cancelled');
    }

    public function testWithdrawalRequestAfterDeadlineFails(): void
    {
        $expiredOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            [
                'orderUrlHash' => $expiredOrder->getUrlHash(),
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
            ],
        );

        $this->assertUserError($response, 'order-withdrawal-deadline-passed');
    }

    public function testWithdrawalRequestAlreadyRequestedFails(): void
    {
        $orderWithWithdrawal = $this->getReferenceForDomain(
            OrderDataFixture::ORDER_WITH_WITHDRAWAL_REQUEST,
            1,
            Order::class,
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            [
                'orderUrlHash' => $orderWithWithdrawal->getUrlHash(),
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
            ],
        );

        $this->assertUserError($response, 'order-withdrawal-already-requested');
    }

    public function testWithdrawalRequestWithInvalidOrderUrlHashFails(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/OrderWithdrawalRequestMutation.graphql',
            [
                'orderUrlHash' => 'non-existent-hash-12345',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
            ],
        );

        $this->assertUserError($response, 'order-not-found');
    }
}
