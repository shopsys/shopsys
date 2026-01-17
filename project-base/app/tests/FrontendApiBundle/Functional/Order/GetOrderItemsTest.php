<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Order\Order;
use App\Model\Product\Product;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Clock\DatePoint;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;

class GetOrderItemsTest extends GraphQlWithLoginTestCase
{
    use OrderItemsTestTrait;

    /**
     * @inject
     */
    private WithdrawalRequestFacade $withdrawalRequestFacade;

    /**
     * @inject
     */
    private WithdrawalRequestDataFactory $withdrawalRequestDataFactory;

    /**
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

    public function testGetOrderItemsExcludesItemsFromOrdersWithWithdrawalRequest(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);

        $this->assertProductExistsInOrderItems($product, true);
        $this->createWithdrawalRequest();
        $this->assertProductExistsInOrderItems($product, false);
    }

    public static function getOrderItemsDataProvider(): iterable
    {
        // last 4 order items
        yield [['last' => 4], [26, 25, 24, 23]];

        // filter by order item type
        yield [['last' => 4, 'filter' => ['type' => OrderItemTypeEnum::TYPE_PRODUCT]], [26, 25, 24, 23]];

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
            [4, 3, 2, 1],
        ];

        // filter by order created after
        yield [
            [
                'last' => 4,
                'filter' => [
                    'orderCreatedAfter' => (new DatePoint())->modify('-1 year')->format(DateTimeInterface::ATOM),
                ],
            ],
            [26, 25, 24, 23],
        ];

        // filter by order status
        yield [['last' => 4, 'filter' => ['orderStatus' => OrderStatusTypeEnum::TYPE_DONE]], [4, 3, 2, 1]];

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

    private function assertProductExistsInOrderItems(Product $product, bool $shouldExist): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderItemsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'orderItems');
        $this->assertProductItemExistsInOrderItemsResponseData($product, $responseData, $shouldExist);
    }
}
