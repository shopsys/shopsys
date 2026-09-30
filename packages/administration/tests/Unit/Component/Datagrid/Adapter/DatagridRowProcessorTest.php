<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatagridRowProcessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;

class DatagridRowProcessorTest extends TestCase
{
    public function testPlainFieldsLeaveTheRowUntouched(): void
    {
        $processor = new DatagridRowProcessor([new FieldDescriptor('name'), new FieldDescriptor('productId', ['property' => 'product.id'])]);

        $row = ['name' => 'Foo', 'product.id' => 1];

        $this->assertSame($row, $processor->process($row));
    }

    public function testCombinedFieldWithoutTemplateIsJoinedWithSpace(): void
    {
        $processor = new DatagridRowProcessor([new FieldDescriptor('customer', ['property' => ['lastName', 'firstName', 'email']])]);

        $row = $processor->process(['lastName' => 'Doe', 'firstName' => 'John', 'email' => null]);

        $this->assertSame('Doe John', $row['customer']);
    }

    public function testCombinedFieldWithTemplateGetsValuesIndexedByProperty(): void
    {
        $processor = new DatagridRowProcessor([new FieldDescriptor('customer', ['property' => ['lastName', 'customerUser.id'], 'template' => 'customer.html.twig'])]);

        $row = $processor->process(['lastName' => 'Doe', 'customerUser.id' => 5]);

        $this->assertSame(['lastName' => 'Doe', 'customerUser.id' => 5], $row['customer']);
    }

    public function testTransformReceivesPropertyValueRowAndResults(): void
    {
        $processor = new DatagridRowProcessor([new FieldDescriptor('isVisible', [
            'property' => 'id',
            'transform' => static fn (mixed $value, array $row, array $results): string => sprintf('%d/%s/%d', $value, $row['name'], count($results)),
        ])]);

        $results = [['id' => 1, 'name' => 'Foo'], ['id' => 2, 'name' => 'Bar']];
        $row = $processor->process($results[0], $results);

        $this->assertSame('1/Foo/2', $row['isVisible']);
        $this->assertSame(1, $row['id']);
    }

    public function testTransformOfCombinedFieldReceivesValuesIndexedByProperty(): void
    {
        $processor = new DatagridRowProcessor([new FieldDescriptor('fullName', [
            'property' => ['firstName', 'lastName'],
            'transform' => static fn (array $value): string => implode(', ', array_reverse($value)),
        ])]);

        $row = $processor->process(['firstName' => 'John', 'lastName' => 'Doe']);

        $this->assertSame('Doe, John', $row['fullName']);
    }
}
