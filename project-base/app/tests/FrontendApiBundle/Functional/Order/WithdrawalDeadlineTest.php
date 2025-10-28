<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class WithdrawalDeadlineTest extends GraphQlTestCase
{
    public function testWithdrawalDeadlineIsNotNullForDeliveredOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/OrderWithdrawalDataQuery.graphql',
            [
                'urlHash' => $order->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'order');

        $this->assertNotNull($data['withdrawalDeadline']);
    }

    public function testWithdrawalDeadlineIsNullForNotDeliveredOrder(): void
    {
        $cancelledOrder = $this->getReferenceForDomain(OrderDataFixture::ORDER_CANCELLED, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/OrderWithdrawalDataQuery.graphql',
            [
                'urlHash' => $cancelledOrder->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'order');

        $this->assertNull($data['withdrawalDeadline']);
    }
}
