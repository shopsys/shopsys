<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Composite;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\LogicalOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridConditionTest extends TestCase
{
    public function testDatagridWithoutConditionAsksForEveryRecord(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);

        $datagrid->createView();

        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testSingleConditionIsPassedAsItIs(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);
        $condition = Condition::equals('deleted', false);

        $datagrid->addCondition($condition);
        $datagrid->createView();

        $this->assertSame($condition, $adapter->getLastRequest()->condition);
    }

    public function testConditionsAreCombinedByConjunction(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);
        $notDeleted = Condition::equals('deleted', false);
        $inStock = Condition::greaterThan('stockQuantity', 0);

        $datagrid->addCondition($notDeleted);
        $datagrid->addCondition($inStock);
        $datagrid->createView();

        $condition = $adapter->getLastRequest()->condition;
        $this->assertInstanceOf(Composite::class, $condition);
        $this->assertSame(LogicalOperatorEnum::AND, $condition->operator);
        $this->assertSame([$notDeleted, $inStock], $condition->conditions);
    }

    /**
     * Rendering the datagrid twice sends the very same request twice — the condition is a part of every
     * request instead of a state of the adapter.
     */
    public function testEveryViewSendsTheSameRequest(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);
        $datagrid->addCondition(Condition::equals('deleted', false));

        $datagrid->createView();
        $datagrid->createView();

        $this->assertCount(2, $adapter->requests);
        $this->assertEquals($adapter->requests[0], $adapter->requests[1]);
    }

    private function createDatagrid(CapturingAdapter $adapter): Datagrid
    {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        return new Datagrid($adapter, $gridFactoryStub, [
            'roleConstant' => 'ROLE_CRUD_TEST',
        ]);
    }
}
