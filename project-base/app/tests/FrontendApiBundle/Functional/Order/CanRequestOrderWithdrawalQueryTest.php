<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CanRequestOrderWithdrawalQueryTest extends GraphQlTestCase
{
    public function testCanRequestWithdrawalForValidOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CanRequestOrderWithdrawalQuery.graphql',
            [
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'canRequestOrderWithdrawal');

        $this->assertTrue($data);
    }

    public function testCannotRequestWithdrawalForCancelledOrder(): void
    {
        $cancelledOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_CANCELLED, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CanRequestOrderWithdrawalQuery.graphql',
            [
                'orderUrlHash' => $cancelledOrder->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'canRequestOrderWithdrawal');

        $this->assertFalse($data);
    }

    public function testCannotRequestWithdrawalAfterDeadline(): void
    {
        $expiredOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CanRequestOrderWithdrawalQuery.graphql',
            [
                'orderUrlHash' => $expiredOrder->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'canRequestOrderWithdrawal');

        $this->assertFalse($data);
    }

    public function testCannotRequestWithdrawalWhenAlreadyRequested(): void
    {
        $orderWithWithdrawal = $this->getReferenceForDomain(
            OrderDataFixture::ORDER_WITH_WITHDRAWAL_REQUEST,
            1,
            Order::class,
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CanRequestOrderWithdrawalQuery.graphql',
            [
                'orderUrlHash' => $orderWithWithdrawal->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'canRequestOrderWithdrawal');

        $this->assertFalse($data);
    }

    public function testCannotRequestWithdrawalForNonExistentOrder(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CanRequestOrderWithdrawalQuery.graphql',
            [
                'orderUrlHash' => 'non-existent-hash-12345',
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'canRequestOrderWithdrawal');

        $this->assertFalse($data);
    }
}
