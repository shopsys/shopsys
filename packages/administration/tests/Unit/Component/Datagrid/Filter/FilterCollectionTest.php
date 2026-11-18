<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;

final class FilterCollectionTest extends TestCase
{
    public function testFilterIsFoundByItsName(): void
    {
        $collection = new FilterCollection();
        $filter = TextFilter::new('author.fullName');

        $collection->add($filter);

        $this->assertTrue($collection->has('author_fullName'));
        $this->assertSame($filter, $collection->get('author_fullName'));
        $this->assertSame($filter, $collection->first());
        $this->assertFalse($collection->isEmpty());
    }

    public function testEmptyCollectionHasNoFirstFilter(): void
    {
        $collection = new FilterCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->first());
    }

    public function testTwoFiltersOfOneNameAreRefused(): void
    {
        $collection = new FilterCollection();
        $collection->add(TextFilter::new('name'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');

        $collection->add(NumericFilter::new('name'));
    }

    public function testFilterReplacesGuessedFilterOfTheSameName(): void
    {
        $collection = new FilterCollection();
        $collection->add(new GuessedFilter(new FieldDescriptor('status', ['filterable' => true])));
        $filter = ChoiceFilter::new('status')->setChoices(['New' => 'new']);

        $collection->add($filter);

        $this->assertSame($filter, $collection->get('status'));
    }

    public function testUnknownFilterIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter "somethingElse" is not declared');

        new FilterCollection()->get('somethingElse');
    }
}
