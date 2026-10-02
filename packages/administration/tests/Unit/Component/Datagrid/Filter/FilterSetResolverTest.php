<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use stdClass;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

final class FilterSetResolverTest extends TestCase
{
    public function testNoFilterIsOfferedWithoutAnyDeclaration(): void
    {
        $filters = $this->resolve([new FieldDescriptor('name')], new FilterCollection());

        $this->assertTrue($filters->isEmpty());
    }

    public function testFilterableFieldGetsFilterPickedByItsPath(): void
    {
        $filters = $this->resolve([new FieldDescriptor('name', ['label' => 'Name', 'filterable' => true])], new FilterCollection());

        $this->assertInstanceOf(TextFilter::class, $filters->get('name'));
        $this->assertSame('Name', $filters->get('name')->getLabel());
    }

    public function testDeclaredFilterReplacesTheOnePickedForTheFieldOfTheSameName(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters->add(ChoiceFilter::new('status')->setChoices(['New' => 'new']));

        $filters = $this->resolve([new FieldDescriptor('status', ['filterable' => true])], $declaredFilters);

        $this->assertInstanceOf(ChoiceFilter::class, $filters->get('status'));
    }

    /**
     * A field whose filter cannot be guessed is never guessed for once a filter is declared for it.
     */
    public function testDeclaredFilterSavesFieldWhoseFilterCannotBeGuessed(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters->add(EntityFilter::new('author'));

        $filters = $this->resolve([new FieldDescriptor('author', ['filterable' => true])], $declaredFilters);

        $this->assertInstanceOf(EntityFilter::class, $filters->get('author'));
    }

    public function testFilterableFieldWithoutKnownFilterIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);

        $this->resolve([new FieldDescriptor('price', ['filterable' => true])], new FilterCollection());
    }

    /**
     * The labels are compared by the rules of the language of the administration — in Czech "Č" sorts between
     * "C" and "D", where a comparison of bytes would put it last.
     */
    public function testFiltersAreOfferedInAlphabeticalOrderOfTheirLabels(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters
            ->add(TextFilter::new('note', 'Čas'))
            ->add(ChoiceFilter::new('status', 'Cena')->setChoices(['New' => 'new']));

        $filters = $this->resolve([
            new FieldDescriptor('name', ['label' => 'Datum', 'filterable' => true]),
            new FieldDescriptor('status', ['label' => 'Stav', 'filterable' => true]),
            new FieldDescriptor('createdAt'),
        ], $declaredFilters);

        $this->assertSame(['status', 'note', 'name'], array_keys(iterator_to_array($filters)));
    }

    public function testFiltersAreAdaptedToTheAdapter(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters->add(TextFilter::new('nope'));

        $this->expectException(FilterNotApplicableException::class);

        $this->resolve([], $declaredFilters);
    }

    /**
     * The offered filters are copies of the declared ones, so one declaration serves several media without one of them
     * changing what the other offers.
     */
    public function testOneDeclarationServesSeveralMedia(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters->add(EntityFilter::new('tags'));
        $resolver = DatagridNarrowingResolverFactory::createFilterSetResolver();

        $toManyFilters = $resolver->resolve($this->createAdapter([
            'tags' => new PathDescription('tags', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION, stdClass::class),
        ]), [], $declaredFilters);
        $toOneFilters = $resolver->resolve($this->createAdapter([
            'tags' => new PathDescription('tags', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION, stdClass::class),
        ]), [], $declaredFilters);

        $this->assertNotSame($declaredFilters->get('tags'), $toManyFilters->get('tags'));
        $this->assertContains(ExpressionOperatorEnum::IS_EMPTY, $toManyFilters->get('tags')->getOperators());
        $this->assertNotContains(ExpressionOperatorEnum::IS_EMPTY, $toOneFilters->get('tags')->getOperators());
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    private function resolve(array $fields, FilterCollection $declaredFilters): FilterCollection
    {
        return DatagridNarrowingResolverFactory::createFilterSetResolver()->resolve($this->createAdapter([
            'name' => new PathDescription('name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
            'note' => new PathDescription('note', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
            'status' => new PathDescription('status', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
            'createdAt' => new PathDescription('createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME),
            'price' => new PathDescription('price', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::MONEY),
            'author' => new PathDescription('author', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION, stdClass::class),
        ]), $fields, $declaredFilters);
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions Every other path is refused, the way a real schema does
     */
    private function createAdapter(array $pathDescriptions): CapturingAdapter
    {
        return new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions, false);
    }
}
