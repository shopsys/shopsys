<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\Model\Order\Order;
use DateTime;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The count is a native query, which has to bind a value of a custom type (money, a date of either class) by its type
 * as well.
 */
class TypedConditionListingTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrmAdapterFactory $ormAdapterFactory;

    /**
     * The adapter resolves the locale of the translated fields from the current request.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
    }

    public function testMoneyNarrowsTheListedOrdersAndTheirCount(): void
    {
        $this->assertListingAndCountAgree(Condition::greaterThan('totalPriceWithVat', Money::zero()));
    }

    /**
     * The creation date of an order is an immutable date and time, while a project may compare it with a mutable one.
     */
    public function testDateOfOtherClassNarrowsTheListedOrdersAndTheirCount(): void
    {
        $this->assertListingAndCountAgree(Condition::lessThan('createdAt', new DateTime('+1 day')));
    }

    private function assertListingAndCountAgree(ConditionInterface $condition): void
    {
        $dataSource = $this->ormAdapterFactory->create(Order::class)->getDatasource(
            new DatasourceRequest('id', [new FieldDescriptor('number')], $condition),
        );

        $listedOrders = $dataSource->getPaginatedRows()->getResults();

        $this->assertNotEmpty($listedOrders, 'The demo data have to contain orders the condition matches.');
        $this->assertSame(count($listedOrders), $dataSource->getTotalRowsCount());
        $this->assertSame(count($listedOrders), $dataSource->getPaginatedRows(1)->getTotalCount());
    }
}
