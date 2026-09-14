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
 * A condition comparing a value of a type of its own — money, a date of either class — narrows the listed records
 * and their count alike. The count is a native query of its own, which has to bind the value by its type as well.
 */
class TypedConditionListingTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private OrmAdapterFactory $ormAdapterFactory;

    /**
     * The adapter resolves the locale of the translated fields from the current request, the same way it
     * does when a listing is rendered.
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
