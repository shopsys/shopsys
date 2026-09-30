<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Component\Grid\Column;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;

class DatagridTest extends TestCase
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Grid\Column>
     */
    private array $columnsById = [];

    private Datagrid $datagrid;

    protected function setUp(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('getDatasource')->willReturn($this->createStub(DataSourceInterface::class));

        $grid = $this->createStub(Grid::class);
        $grid->method('addColumn')->willReturnCallback(function (string $id, string $sourceColumnName, string $title, bool $sortable, array $options): Column {
            $this->columnsById[$id] = new Column($id, $sourceColumnName, $title, $sortable, $options);

            return $this->columnsById[$id];
        });
        $grid->method('createView')->willReturn($this->createStub(GridView::class));

        $gridFactory = $this->createStub(GridFactory::class);
        $gridFactory->method('create')->willReturn($grid);

        $this->datagrid = new Datagrid($adapter, $gridFactory, ['roleConstant' => 'ROLE_CRUD_TEST']);
    }

    public function testCombinedFieldIsOrderedByAllProperties(): void
    {
        $this->datagrid->add('customer', ['property' => ['lastName', 'firstName']]);

        $this->datagrid->createView();

        $this->assertSame('customer', $this->columnsById['customer']->getSourceColumnName());
        $this->assertSame('lastName,firstName', $this->columnsById['customer']->getOrderSourceColumnName());
    }
}
