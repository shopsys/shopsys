<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use stdClass;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

final class FilterSetResolverTest extends TestCase
{
    public function testNoFilterIsOfferedWithoutAnyDeclaration(): void
    {
        $filters = $this->resolve(new FilterCollection());

        $this->assertTrue($filters->isEmpty());
    }

    public function testGuessedFilterIsReplacedByFilterPickedByThePathOfItsField(): void
    {
        $filters = $this->resolve(new FilterCollection()->add($this->createGuessedFilter('name', 'Name')));

        $this->assertInstanceOf(TextFilter::class, $filters->get('name'));
        $this->assertSame('Name', $filters->get('name')->getLabel());
    }

    /**
     * The labels are compared by the rules of the language of the administration — in Czech "Č" sorts between
     * "C" and "D", where a comparison of bytes would put it last.
     */
    public function testFiltersAreOfferedInAlphabeticalOrderOfTheirLabels(): void
    {
        $filters = $this->resolve(new FilterCollection()
            ->add($this->createGuessedFilter('name', 'Datum'))
            ->add($this->createGuessedFilter('status', 'Stav'))
            ->add(TextFilter::new('note', 'Čas'))
            ->add(ChoiceFilter::new('status', 'Cena')->setChoices(['New' => 'new'])));

        $this->assertSame(['status', 'note', 'name'], array_keys(iterator_to_array($filters)));
    }

    /**
     * The offered filters are copies of the declared ones, so resolving them for one adapter does not change what
     * another adapter offers.
     */
    public function testOneDeclarationServesSeveralAdapters(): void
    {
        $declaredFilters = new FilterCollection();
        $declaredFilters->add(EntityFilter::new('tags'));
        $resolver = DatagridStateFactoryFixture::createFilterSetResolver();

        $toManyFilters = $resolver->resolve($this->createAdapter([
            'tags' => new PathDescription('tags', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION, stdClass::class),
        ]), $declaredFilters);
        $toOneFilters = $resolver->resolve($this->createAdapter([
            'tags' => new PathDescription('tags', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION, stdClass::class),
        ]), $declaredFilters);

        $this->assertNotSame($declaredFilters->get('tags'), $toManyFilters->get('tags'));
        $this->assertSame([
            ExpressionOperatorEnum::IN,
            ExpressionOperatorEnum::NOT_IN,
            ExpressionOperatorEnum::IS_EMPTY,
            ExpressionOperatorEnum::IS_NOT_EMPTY,
        ], $toManyFilters->get('tags')->getOperators());
        $this->assertSame([
            ExpressionOperatorEnum::IN,
            ExpressionOperatorEnum::NOT_IN,
            ExpressionOperatorEnum::IS_NULL,
            ExpressionOperatorEnum::IS_NOT_NULL,
        ], $toOneFilters->get('tags')->getOperators());
    }

    private function createGuessedFilter(string $fieldName, ?string $label = null): GuessedFilter
    {
        return new GuessedFilter(new FieldDescriptor($fieldName, ['label' => $label ?? $fieldName, 'filterable' => true]));
    }

    private function resolve(FilterCollection $filters): FilterCollection
    {
        return DatagridStateFactoryFixture::createFilterSetResolver()->resolve($this->createAdapter([
            'name' => new PathDescription('name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
            'note' => new PathDescription('note', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
            'status' => new PathDescription('status', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING),
        ]), $filters);
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions Every other path is refused, the way a real schema does
     */
    private function createAdapter(array $pathDescriptions): CapturingAdapter
    {
        return new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions, false);
    }
}
