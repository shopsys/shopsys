<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\Model\Order\Order;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class GetOrderItemsTest extends GraphQlWithLoginTestCase
{
    use OrderItemsTestTrait;

    /**
     * @param array $queryVariables
     * @param array $expectedOrderItemsIds
     */
    #[DataProvider('getOrderItemsDataProvider')]
    public function testGetOrderItems(
        array $queryVariables,
        array $expectedOrderItemsIds = [],
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetOrderItemsQuery.graphql', $queryVariables);

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
        yield [['filter' => ['orderUuid' => '7f410b92-19e8-52ef-aca9-a482fd6daf74']], [1, 2, 3, 4]];

        // filter by order created after
        yield [['first' => 4, 'filter' => ['orderCreatedAfter' => (new DateTime('-1 year'))->format(DateTimeInterface::ATOM)]], [1, 2, 3, 4]];

        // filter by order status
        yield [['first' => 4, 'filter' => ['orderStatus' => OrderStatusTypeEnum::TYPE_DONE]], [1, 2, 3, 4]];

        // filter by order item catnum
        yield [['first' => 4, 'filter' => ['catnum' => '9184535']], [1]];

        // filter by order item product uuid
        yield [['first' => 4, 'filter' => ['productUuid' => 'eec9c0fc-6ba4-5511-8bc7-5f280dc9c99e']], [1]];
    }
}
