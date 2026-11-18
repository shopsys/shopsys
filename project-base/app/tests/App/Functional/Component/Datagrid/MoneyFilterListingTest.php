<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Closure;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterSetResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\MoneyFilter;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The money input of the application turns the typed text into money, which the query compares with the money column.
 */
class MoneyFilterListingTest extends TransactionFunctionalTestCase
{
    private const string FIELD_NAME = 'totalPriceWithVat';

    /**
     * @inject
     */
    private OrmAdapterFactory $ormAdapterFactory;

    /**
     * @inject
     */
    private FilterSetResolver $filterSetResolver;

    /**
     * @inject
     */
    private FilterFormFactory $filterFormFactory;

    /**
     * The adapter resolves the locale of the translated fields from the current request.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    public function testRangeOfAmountsNarrowsTheListedOrders(): void
    {
        $expectedOrderIds = $this->getIdsOfOrdersWhoseTotalPrice(
            static fn (Money $totalPrice): bool => $totalPrice->isGreaterThanOrEqualTo(Money::create('100.5'))
                && $totalPrice->isLessThanOrEqualTo(Money::create(10000)),
        );

        $this->assertSame(
            $expectedOrderIds,
            $this->listIdsOfOrdersFilteredBy(ExpressionOperatorEnum::BETWEEN, [
                'from' => '100,5',
                'to' => '10000',
            ]),
        );
    }

    public function testAmountListsTheOrdersOfTheSameTotalPrice(): void
    {
        $totalPrice = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1', Order::class)->getTotalPriceWithVat();
        $expectedOrderIds = $this->getIdsOfOrdersWhoseTotalPrice(
            static fn (Money $orderTotalPrice): bool => $orderTotalPrice->equals($totalPrice),
        );

        $this->assertSame(
            $expectedOrderIds,
            $this->listIdsOfOrdersFilteredBy(ExpressionOperatorEnum::EQUALS, $totalPrice->getAmount()),
        );
    }

    /**
     * @param string|array<string, string> $value The text typed by the administrator, a pair of bounds for a range
     * @return int[]
     */
    private function listIdsOfOrdersFilteredBy(string $operator, string|array $value): array
    {
        $adapter = $this->ormAdapterFactory->create(Order::class);
        $field = new FieldDescriptor(self::FIELD_NAME, ['filterable' => true]);
        $filters = $this->filterSetResolver->resolve($adapter, new FilterCollection()->add(new GuessedFilter($field)));
        $this->assertInstanceOf(MoneyFilter::class, $filters->get(self::FIELD_NAME));

        $form = $this->filterFormFactory->createForm($filters, 'Order_filter', [
            'groups' => [['rules' => [['filter' => self::FIELD_NAME, 'operator' => $operator, 'value' => $value]]]],
        ]);
        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));

        $rows = $adapter->getDatasource(new DatasourceRequest('id', [$field], new FilterPanel($form, $filters)->createCondition()))
            ->getPaginatedRows()
            ->getResults();
        $orderIds = array_map(static fn (array $row): int => (int)$row['id'], $rows);
        sort($orderIds);

        return $orderIds;
    }

    /**
     * @param \Closure(\Shopsys\FrameworkBundle\Component\Money\Money): bool $condition
     * @return int[]
     */
    private function getIdsOfOrdersWhoseTotalPrice(Closure $condition): array
    {
        $orderIds = [];
        $orders = $this->em->getRepository(Order::class)->findAll();

        foreach ($orders as $order) {
            if ($condition($order->getTotalPriceWithVat())) {
                $orderIds[] = $order->getId();
            }
        }

        $this->assertNotEmpty($orderIds, 'The demo data have to contain orders the condition matches.');
        $this->assertLessThan(count($orders), count($orderIds), 'The demo data have to contain orders the condition does not match.');
        sort($orderIds);

        return $orderIds;
    }
}
