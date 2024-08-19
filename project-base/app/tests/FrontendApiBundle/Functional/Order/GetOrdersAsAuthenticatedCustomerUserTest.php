<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    private const EXPECTED_ORDER_IDS = [4, 5, 3, 1, 2, 6];

    /**
     * @param array $queryVariables
     * @param int|null $offsetInExpected
     * @param int|null $lengthInExpected
     */
    #[DataProvider('getOrdersDataProvider')]
    public function testGetAllCustomerUserOrders(
        array $queryVariables,
        ?int $offsetInExpected,
        ?int $lengthInExpected,
    ): void {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/getOrders.graphql', $resolvedQueryVariables);

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
    public static function getOrdersDataProvider(): iterable
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

        // filter by order item catnum
        yield [
            [
                'first' => 2,
                'filter' => [
                    'orderItemsCatnum' => new ReferenceDataAccessor(
                        OrderDataFixture::ORDER_PREFIX . 2,
                        fn (Order $order) => $order->getProductItems()[1]->getProduct()->getCatnum(),
                    ),
                ],
            ],
            4,
            1,
        ];

        // filter by order item product uuid
        yield [
            [
                'first' => 1,
                'filter' => [
                    'orderItemsProductUuid' => new ReferenceDataAccessor(
                        OrderDataFixture::ORDER_PREFIX . 2,
                        fn (Order $order) => $order->getProductItems()[0]->getProduct()->getUuid(),
                    ),
                ],
            ],
            4,
            1,
        ];

        // filter by order created after date
        yield [['filter' => ['createdAfter' => (new DateTime('-1 year'))->format(DateTimeInterface::ATOM)]], null, null];

        // filter by order status
        yield [['filter' => ['status' => 'inProgress']], 0, 1];
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
