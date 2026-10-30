<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use App\Model\Product\Product;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Clock\DatePoint;
use Tests\FrontendApiBundle\Test\CustomerUserRoleGroupTestHelper;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\ReferenceDataAccessor;
use Tests\FrontendApiBundle\Test\SearchInputTestUtils;

class SearchOrderItemsTest extends GraphQlWithLoginTestCase
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
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private CustomerUserRoleGroupTestHelper $customerUserRoleGroupTestHelper;

    /**
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

    public function testSearchOrderItemsExcludesItemsFromOrdersWithWithdrawalRequest(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);

        $this->assertProductExistsInOrderItems($product, true);
        $this->createWithdrawalRequest();
        $this->assertProductExistsInOrderItems($product, false);
    }

    public function testSearchOrderItemsExcludesItemsFromDeletedOrders(): void
    {
        $this->assertItemsOfDeletedOrderAreExcluded();
    }

    public function testSearchOrderItemsWithoutCompanyOrdersViewExcludesItemsFromDeletedOrders(): void
    {
        $this->logout();
        $this->customerUserRoleGroupTestHelper->assignRoleGroup(
            $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1, CustomerUser::class),
            $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class),
        );
        $this->login();

        $this->assertItemsOfDeletedOrderAreExcluded();
    }

    public function testSearchOrderItemsRejectsPageSizeOverMaximumAllowedLimit(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/SearchOrderItemsQuery.graphql',
            ['first' => 101, ...SearchInputTestUtils::createSearchInputQueryVariables('')],
        );

        $this->assertUserError($response, 'max-allowed-limit');
    }

    public static function getOrderItemsDataProvider(): iterable
    {
        // last 4 order items
        yield [['last' => 4, ...SearchInputTestUtils::createSearchInputQueryVariables('')], [26, 25, 24, 23]];

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
                'filter' => ['orderCreatedAfter' => (new DatePoint())->modify('-1 year')->format(DateTimeInterface::ATOM)],
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

    private function assertItemsOfDeletedOrderAreExcluded(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 5, Order::class);

        $responseDataBeforeDeletion = $this->getOrderItemsSearchResponseData();
        $this->assertProductItemExistsInOrderItemsResponseData($product, $responseDataBeforeDeletion, true);

        $this->orderFacade->deleteById($order->getId());

        $responseDataAfterDeletion = $this->getOrderItemsSearchResponseData();
        $this->assertProductItemExistsInOrderItemsResponseData($product, $responseDataAfterDeletion, false);
        $this->assertSame(
            $responseDataBeforeDeletion['totalCount'] - count($order->getItems()),
            $responseDataAfterDeletion['totalCount'],
        );
    }

    private function assertProductExistsInOrderItems(Product $product, bool $shouldExist): void
    {
        $this->assertProductItemExistsInOrderItemsResponseData($product, $this->getOrderItemsSearchResponseData(), $shouldExist);
    }

    private function getOrderItemsSearchResponseData(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SearchOrderItemsQuery.graphql', SearchInputTestUtils::createSearchInputQueryVariables(''));

        return $this->getResponseDataForGraphQlType($response, 'orderItemsSearch');
    }
}
