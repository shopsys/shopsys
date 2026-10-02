<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Exception\RowNotFoundInGridByIdException;

class ArrayAdapterTest extends TestCase
{
    private const array DATA = [
        ['id' => 1, 'lastName' => 'Doe', 'firstName' => 'John', 'product' => ['id' => 10, 'name' => 'Foo']],
        ['id' => 2, 'lastName' => 'Doe', 'firstName' => 'Jane', 'product' => ['id' => 11, 'name' => 'Bar']],
        ['id' => 3, 'lastName' => 'Adams', 'firstName' => 'Zoe', 'product' => null],
    ];

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    private function createDataSource(array $fields): DataSourceInterface
    {
        return new ArrayAdapter(self::DATA)->getDatasource('id', $fields);
    }

    public function testDotNotationIsResolvedFromNestedArrays(): void
    {
        $dataSource = $this->createDataSource([
            new FieldDescriptor('productName', ['property' => 'product.name']),
            new FieldDescriptor('productId', ['property' => 'product.id']),
        ]);

        $rows = $dataSource->getPaginatedRows()->getResults();

        $this->assertSame('Foo', $rows[0]['product.name']);
        $this->assertSame(10, $rows[0]['product.id']);
        $this->assertNull($rows[2]['product.name']);
    }

    public function testRowsAreOrderedByAllPropertiesOfCombinedField(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('customer', ['property' => ['lastName', 'firstName']])]);

        $ascending = $dataSource->getPaginatedRows(null, 1, 'lastName,firstName', DataSourceInterface::ORDER_ASC)->getResults();
        $descending = $dataSource->getPaginatedRows(null, 1, 'lastName,firstName', DataSourceInterface::ORDER_DESC)->getResults();

        $this->assertSame(['Adams Zoe', 'Doe Jane', 'Doe John'], array_column($ascending, 'customer'));
        $this->assertSame(['Doe John', 'Doe Jane', 'Adams Zoe'], array_column($descending, 'customer'));
    }

    public function testRowsAreOrderedByNestedProperty(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('productName', ['property' => 'product.name'])]);

        $rows = $dataSource->getPaginatedRows(null, 1, 'product.name', DataSourceInterface::ORDER_ASC)->getResults();

        $this->assertSame([3, 2, 1], array_column($rows, 'id'));
    }

    public function testRowsArePaginated(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('lastName')]);

        $paginationResult = $dataSource->getPaginatedRows(2, 2, 'id', DataSourceInterface::ORDER_ASC);

        $this->assertSame(3, $paginationResult->getTotalCount());
        $this->assertSame(2, $paginationResult->getPage());
        $this->assertSame([3], array_column($paginationResult->getResults(), 'id'));
        $this->assertSame(3, $dataSource->getTotalRowsCount());
    }

    public function testTransformReceivesRowsOfThePage(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('position', [
            'property' => 'id',
            'transform' => static fn (int $id, array $row, array $results): string => sprintf('%d of %d', $id, count($results)),
        ])]);

        $rows = $dataSource->getPaginatedRows(2, 1, 'id', DataSourceInterface::ORDER_ASC)->getResults();

        $this->assertSame(['1 of 2', '2 of 2'], array_column($rows, 'position'));
    }

    public function testOneRowIsProcessedToo(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('customer', ['property' => ['lastName', 'firstName']])]);

        $this->assertSame('Adams Zoe', $dataSource->getOneRow(3)['customer']);

        $this->expectException(RowNotFoundInGridByIdException::class);
        $dataSource->getOneRow(4);
    }

    public function testVirtualFieldIsNotResolved(): void
    {
        $dataSource = $this->createDataSource([new FieldDescriptor('productName', ['virtual' => true, 'property' => 'product.name'])]);

        $rows = $dataSource->getPaginatedRows()->getResults();

        $this->assertArrayNotHasKey('product.name', $rows[0]);
    }
}
