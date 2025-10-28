<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class WithdrawalInstructionsTest extends GraphQlTestCase
{
    public function testGetWithdrawalInstructionsForOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/OrderWithdrawalDataQuery.graphql',
            [
                'urlHash' => $order->getUrlHash(),
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'order');

        $this->assertIsString($data['withdrawalInstructions']);
        $this->assertStringContainsString($order->getNumber(), $data['withdrawalInstructions']);
    }
}
