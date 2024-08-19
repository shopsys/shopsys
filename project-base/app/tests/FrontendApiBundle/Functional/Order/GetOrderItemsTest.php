<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Order\Order;
use App\Model\Product\Product;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetOrderItemsTest extends GraphQlWithLoginTestCase
{
    use OrderItemsTestTrait;

    /**
     * @param array $queryVariables
     * @param int[] $expectedOrderItemsIds
     */
    #[DataProvider('getOrderItemsDataProvider')]
    public function testGetOrderItems(
        array $queryVariables,
        array $expectedOrderItemsIds = [],
    ): void {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetOrderItemsQuery.graphql',
            $resolvedQueryVariables,
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'orderItems');

        $expectedOrderItems = $this->getExpectedOrderItems($expectedOrderItemsIds);

        $this->assertOrderItemConnection($responseData, $expectedOrderItems, $expectedOrderItemsIds);
    }

    /**
     * @return iterable
     */
    public static function getOrderItemsDataProvider(): iterable
    {
        // first 4 order items
        yield [['first' => 4], [1, 2, 3, 4]];

        // filter by order item type
        yield [['first' => 4, 'filter' => ['type' => OrderItemTypeEnum::TYPE_PRODUCT]], [1, 2, 5, 6]];

        // filter by order uuid
        yield [
            [
                'filter' => [
                    'orderUuid' => new ReferenceDataAccessor(
                        OrderDataFixture::ORDER_PREFIX . 1,
                        fn (Order $order) => $order->getUuid(),
                    ),
                ],
            ],
            [1, 2, 3, 4],
        ];

        // filter by order created after
        yield [['first' => 4, 'filter' => ['orderCreatedAfter' => (new DateTime('-1 year'))->format(DateTimeInterface::ATOM)]], [1, 2, 3, 4]];

        // filter by order status
        yield [['first' => 4, 'filter' => ['orderStatus' => OrderStatusTypeEnum::TYPE_DONE]], [1, 2, 3, 4]];

        // filter by order item catnum
        yield [
            [
                'first' => 4,
                'filter' => [
                    'catnum' => new ReferenceDataAccessor(
                        ProductDataFixture::PRODUCT_PREFIX . 9,
                        fn (Product $product) => $product->getCatnum(),
                    ),
                ],
            ],
            [1],
        ];

        // filter by order item product uuid
        yield [
            [
                'first' => 4,
                'filter' => [
                    'productUuid' => new ReferenceDataAccessor(
                        ProductDataFixture::PRODUCT_PREFIX . 9,
                        fn (Product $product) => $product->getUuid(),
                    ),
                ],
            ],
            [1],
        ];
    }
}
