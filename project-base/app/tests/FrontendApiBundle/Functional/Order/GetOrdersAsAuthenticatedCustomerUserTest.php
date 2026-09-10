<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\Model\Order\Order;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Symfony\Component\Clock\DatePoint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetOrdersAsAuthenticatedCustomerUserTest extends GraphQlWithLoginTestCase
{
    use OrderTestTrait;

    private const EXPECTED_ORDER_IDS = [4, 5, 3, 1, 46, 2, 6];

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

    public static function getOrdersDataProvider(): iterable
    {
        // all orders
        yield [[], null, null];

        // first 2 orders
        yield [['first' => 2], 0, 2];

        // first 1 order
        yield [['first' => 1], 0, 1];

        // last 1 order
        yield [['last' => 1], 6, 1];

        // last 2 orders
        yield [['last' => 2], 5, 2];

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
            5,
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
            5,
            1,
        ];

        // filter by order created after date
        yield [['filter' => ['createdAfter' => (new DatePoint())->modify('-1 year')->format(DateTimeInterface::ATOM)]], null, null];

        // filter by order created before date
        yield [['filter' => ['createdBefore' => (new DatePoint())->modify('-10 days')->format(DateTimeInterface::ATOM)]], 5, 2];

        // filter by order number search
        yield [
            [
                'filter' => [
                    'search' => new ReferenceDataAccessor(
                        OrderDataFixture::ORDER_PREFIX . 2,
                        fn (Order $order) => $order->getNumber(),
                    ),
                ],
            ],
            5,
            1,
        ];

        // filter by order item product name search
        yield [
            [
                'filter' => [
                    'search' => 'Hello Kitty',
                ],
            ],
            0,
            2,
        ];

        // filter by order status
        yield [
            [
                'filter' => [
                    'statusCodes' => [
                        self::createOrderStatusCodeAccessor(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS),
                    ],
                ],
            ],
            0,
            1,
        ];

        // filter by multiple order statuses
        yield [
            [
                'filter' => [
                    'statusCodes' => [
                        self::createOrderStatusCodeAccessor(OrderStatusDataFixture::ORDER_STATUS_NEW),
                        self::createOrderStatusCodeAccessor(OrderStatusDataFixture::ORDER_STATUS_DONE),
                    ],
                ],
            ],
            1,
            6,
        ];
    }

    /**
     * @param array<string, mixed> $queryVariables
     * @param array<string, int> $expectedCountsByStatusReferenceName
     */
    #[DataProvider('getOrderStatusCountsDataProvider')]
    public function testGetOrderStatusCounts(array $queryVariables, array $expectedCountsByStatusReferenceName): void
    {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/getOrders.graphql', $resolvedQueryVariables);
        $actualCountsByStatusCode = $this->getOrderStatusCountsByStatusCode($response);

        foreach ($expectedCountsByStatusReferenceName as $orderStatusReferenceName => $expectedCount) {
            $orderStatus = $this->getReference($orderStatusReferenceName, OrderStatus::class);

            $this->assertSame($expectedCount, $actualCountsByStatusCode[$orderStatus->getCode()]);
        }
    }

    /**
     * @return iterable<string, array{
     *     0: array<string, mixed>,
     *     1: array<string, int>,
     * }>
     */
    public static function getOrderStatusCountsDataProvider(): iterable
    {
        yield 'all order status counts' => [
            [
                'first' => 1,
            ],
            [
                OrderStatusDataFixture::ORDER_STATUS_NEW => 3,
                OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS => 1,
                OrderStatusDataFixture::ORDER_STATUS_DONE => 3,
                OrderStatusDataFixture::ORDER_STATUS_CANCELED => 0,
                OrderStatusDataFixture::ORDER_STATUS_WITHDRAWN => 0,
            ],
        ];

        yield 'status counts respect search and status filters' => [
            [
                'first' => 1,
                'statuslessFilter' => [
                    'search' => 'Hello Kitty',
                    'statusCodes' => [
                        self::createOrderStatusCodeAccessor(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS),
                    ],
                ],
            ],
            [
                OrderStatusDataFixture::ORDER_STATUS_NEW => 0,
                OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS => 1,
                OrderStatusDataFixture::ORDER_STATUS_DONE => 0,
            ],
        ];
    }

    private static function createOrderStatusCodeAccessor(string $referenceName): ReferenceDataAccessor
    {
        return new ReferenceDataAccessor(
            $referenceName,
            fn (OrderStatus $orderStatus) => $orderStatus->getCode(),
        );
    }

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

    /**
     * @param array<string, mixed> $response
     * @return array<string, int>
     */
    private function getOrderStatusCountsByStatusCode(array $response): array
    {
        $statusCounts = $this->getResponseDataForGraphQlType($response, 'orderStatusCounts');
        $statusCountsByStatusCode = [];

        foreach ($statusCounts as $statusCount) {
            $statusCountsByStatusCode[$statusCount['status']['code']] = $statusCount['count'];
        }

        return $statusCountsByStatusCode;
    }
}
