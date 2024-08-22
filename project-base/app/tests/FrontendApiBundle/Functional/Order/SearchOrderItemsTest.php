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
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;
use Tests\FrontendApiBundle\Test\SearchInputTestUtils;

class SearchOrderItemsTest extends GraphQlWithLoginTestCase
{
    use OrderItemsTestTrait;

    /**
     * @param array $queryVariables
     * @param int[] $expectedOrderItemsIds
     */
    #[DataProvider('getOrderItemsDataProvider')]
    public function testSearchOrderItems(
        array $queryVariables,
        array $expectedOrderItemsIds = [],
    ): void {
        $resolvedQueryVariables = $this->resolveReferenceDataAccessors($queryVariables);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/SearchOrderItemsQuery.graphql',
            $resolvedQueryVariables,
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderItemsSearch');

        $expectedOrderItems = $this->getExpectedOrderItems($expectedOrderItemsIds);

        $this->assertOrderItemConnection($responseData, $expectedOrderItems, $expectedOrderItemsIds);
    }

    /**
     * @return iterable
     */
    public static function getOrderItemsDataProvider(): iterable
    {
        // first 4 order items
        yield [['first' => 4, ...SearchInputTestUtils::createSearchInputQueryVariables('')], [1, 2, 3, 4]];

        // search by name and filter by order item type
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('Hello Kitty'),
                'filter' => ['type' => OrderItemTypeEnum::TYPE_PRODUCT],
            ],
            [15, 20],
        ];

        // search by name and filter by order uuid
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('A4tech'),
                'filter' => [
                    'orderUuid' => new ReferenceDataAccessor(
                        OrderDataFixture::ORDER_PREFIX . 3,
                        fn (Order $order) => $order->getUuid(),
                    ),
                ],
            ],
            [11],
        ];

        // search by name and filter by order created after
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('Hello Kitty'),
                'filter' => ['orderCreatedAfter' => (new DateTime('-1 year'))->format(DateTimeInterface::ATOM)],
            ],
            [15, 20],
        ];

        // search by catnum and filter by order status
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('9177759'),
                'filter' => ['orderStatus' => OrderStatusTypeEnum::TYPE_DONE],
            ],
            [20],
        ];

        // search by name and filter by order item catnum
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('Hello Kitty'),
                'filter' => [
                    'catnum' => new ReferenceDataAccessor(
                        ProductDataFixture::PRODUCT_PREFIX . 1,
                        fn (Product $product) => $product->getCatnum(),
                    ),
                ],
            ],
            [15, 20],
        ];

        // search by catnum and filter by order item product uuid
        yield [
            [
                ...SearchInputTestUtils::createSearchInputQueryVariables('9177759'),
                'filter' => [
                    'productUuid' => new ReferenceDataAccessor(
                        ProductDataFixture::PRODUCT_PREFIX . 1,
                        fn (Product $product) => $product->getUuid(),
                    ),
                ],
            ],
            [15, 20],
        ];
    }
}
