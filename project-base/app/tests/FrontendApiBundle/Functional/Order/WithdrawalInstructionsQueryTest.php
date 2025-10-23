<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class WithdrawalInstructionsQueryTest extends GraphQlTestCase
{
    public function testGetWithdrawalInstructionsForValidOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_YESTERDAY, 1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/WithdrawalInstructionsQuery.graphql',
            [
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $data = $response['data']['withdrawalInstructions'];

        $this->assertIsString($data);
        $this->assertStringContainsString($order->getNumber(), $data);
    }

    public function testGetWithdrawalInstructionsForNonExistentOrder(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/WithdrawalInstructionsQuery.graphql',
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
