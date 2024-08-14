<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\Model\Order\Order;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class SearchOrderItemsTest extends GraphQlWithLoginTestCase
{
    use OrderItemsTestTrait;

    /**
     * @param array $queryVariables
     * @param array $expectedOrderItemsIds
     */
    #[DataProvider('getOrderItemsDataProvider')]
    public function testSearchOrderItems(
        array $queryVariables,
        array $expectedOrderItemsIds = [],
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SearchOrderItemsQuery.graphql', $queryVariables);
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
        yield [['first' => 4, ...self::createSearchInput('')], [1, 2, 3, 4]];

        // search by name and filter by order item type
        yield [
            [...self::createSearchInput('Hello Kitty'), 'filter' => ['type' => OrderItemTypeEnum::TYPE_PRODUCT]],
            [15, 20],
        ];

        // search by name and filter by order uuid
        yield [
            [...self::createSearchInput('A4tech'), 'filter' => ['orderUuid' => '7cb7934c-72bc-59e3-8834-8e0fd8c02920']],
            [11],
        ];

        // search by name and filter by order created after
        yield [
            [
                ...self::createSearchInput('Hello Kitty'),
                'filter' => ['orderCreatedAfter' => (new DateTime('-1 year'))->format(DateTimeInterface::ATOM)],
            ],
            [15, 20],
        ];

        // search by catnum and filter by order status
        yield [
            [...self::createSearchInput('9177759'), 'filter' => ['orderStatus' => OrderStatusTypeEnum::TYPE_DONE]],
            [20],
        ];

        // search by name and filter by order item catnum
        yield [
            [...self::createSearchInput('Hello Kitty'), 'filter' => ['catnum' => '9177759']],
            [15, 20],
        ];

        // search by catnum and filter by order item product uuid
        yield [
            [
                ...self::createSearchInput('9177759'),
                'filter' => ['productUuid' => '55bb22ab-bb88-5459-a464-005b948d8c78'],
            ],
            [15, 20],
        ];
    }

    /**
     * @param string $search
     * @return array
     */
    private static function createSearchInput(string $search): array
    {
        return ['searchInput' => ['search' => $search, 'isAutocomplete' => false, 'userIdentifier' => Uuid::uuid4()]];
    }
}
