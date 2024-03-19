<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    private const EXPECTED_ORDER_IDS = [4, 5, 3, 1, 2, 6];

    /**
     * @dataProvider getOrdersDataProvider
     * @param array $queryVariables
     * @param int|null $offsetInExpected
     * @param int|null $lengthInExpected
     */
    public function testGetAllCustomerUserOrders(
        array $queryVariables,
        ?int $offsetInExpected,
        ?int $lengthInExpected,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/getOrders.graphql', $queryVariables);

        $responseData = $this->getResponseDataForGraphQlType($response, 'orders');

        $expectedUserOrders = $this->getExpectedUserOrders($offsetInExpected, $lengthInExpected);

        $this->assertArrayHasKey('edges', $responseData);
        $this->assertSameSize(
            $expectedUserOrders,
            $responseData['edges'],
        );

        foreach ($responseData['edges'] as $orderIndex => $edge) {
            $orderMessage = sprintf(
                'Hint: check data and sort of order with ID #%d',
                self::EXPECTED_ORDER_IDS[$orderIndex + $offsetInExpected],
            );


            $this->assertArrayHasKey('node', $edge, $orderMessage);

            $expectedOrderData = array_shift($expectedUserOrders);
            $this->assertArrayHasKey('status', $edge['node'], $orderMessage);
            $this->assertSame($expectedOrderData['status'], $edge['node']['status'], $orderMessage);

            $this->assertArrayHasKey('totalPrice', $edge['node'], $orderMessage);
            $this->assertArrayHasKey('priceWithVat', $edge['node']['totalPrice'], $orderMessage);
            $this->assertSame($expectedOrderData['priceWithVat'], $edge['node']['totalPrice']['priceWithVat'], $orderMessage);
        }
    }

    /**
     * @return iterable
     */
    public function getOrdersDataProvider(): iterable
    {
        // all orders
        yield [[], null, null];

        // first 2 orders
        yield [['first' => 2], 0, 2];

        // first 1 order
        yield [['first' => 1], 0, 1];

        // last 1 order
        yield [['last' => 1], 5, 1];

        //last 2 orders
        yield [['last' => 2], 4, 2];
    }

    /**
     * @param int|null $offset
     * @param int|null $length
     * @return array
     */
    private function getExpectedUserOrders(?int $offset, ?int $length): array
    {
        $ordersArray = [];

        foreach (self::EXPECTED_ORDER_IDS as $orderId) {
            $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . $orderId, Order::class);

            $ordersArray[] = [
                'status' => $order->getStatus()->getName(),
                'priceWithVat' => $order->getTotalPriceWithVat()->getAmount(),
            ];
        }

        if ($offset !== null) {
            return array_slice($ordersArray, $offset, $length);
        }

        return $ordersArray;
    }
}
