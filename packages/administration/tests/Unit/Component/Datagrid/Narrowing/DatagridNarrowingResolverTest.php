<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Narrowing;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowing;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The rules of the searchable fields and of the offered filters are covered by `SearchablePathResolverTest` and
 * `FilterSetResolverTest`; this asserts how the narrowing is put together from them and from the request.
 */
final class DatagridNarrowingResolverTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);
    }

    public function testNothingNarrowsWithoutAnyCondition(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState());

        $this->assertEquals(new DatagridNarrowing(), $narrowing);
    }

    public function testSingleConditionIsPassedAsItIs(): void
    {
        $scope = $this->createScope('domainId');

        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(), domainControlScope: $scope);

        $this->assertSame($scope, $narrowing->domainControlScope);
        $this->assertEquals($scope->createCondition(), $narrowing->condition);
    }

    public function testDomainControlOfEntityWithoutDomainNarrowsNothing(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(), domainControlScope: $this->createScope(null));

        $this->assertNull($narrowing->condition);
    }

    public function testDomainAndSearchAreCombinedByConjunction(): void
    {
        $scope = $this->createScope('domainId');

        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(['text' => 'hrnek']), [$this->createSearchableField('name')], domainControlScope: $scope);

        $this->assertEquals(
            Condition::andX($scope->createCondition(), Condition::orX(Condition::contains('name', 'hrnek'))),
            $narrowing->condition,
        );
    }

    public function testComposedRulesNarrowTheRecords(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(null, ['operator' => 'and', 'groups' => [
            ['operator' => 'or', 'rules' => [
                ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                ['filter' => 'price', 'operator' => 'greaterThan', 'value' => '100'],
            ]],
        ]]), declaredFilters: $this->createFilters());

        $this->assertTrue($narrowing->filterActive);
        $this->assertSame('Product_filter', $narrowing->filterPanel?->form->getName());
        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100.0)),
            $narrowing->condition,
        );
    }

    /**
     * The filter and the quick search are two ways of asking the same question, so a composed filter wins and the
     * quick search is not applied, while its form still shows what was searched for.
     */
    public function testComposedFilterOutranksTheQuickSearch(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(
            ['text' => 'talíř'],
            ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'equals', 'value' => 'Hrnek']]]]],
        ), [$this->createSearchableField('name')], $this->createFilters());

        $this->assertTrue($narrowing->filterActive);
        $this->assertSame('talíř', $narrowing->quickSearch?->term);
        $this->assertEquals(Condition::equals('name', 'Hrnek'), $narrowing->condition);
    }

    public function testQuickSearchAppliesWhenNoRuleIsComposed(): void
    {
        $narrowing = $this->resolve(
            DatagridNarrowingResolverFactory::createRequestState(['text' => 'talíř'], ['groups' => []]),
            [$this->createSearchableField('name')],
            $this->createFilters(),
        );

        $this->assertFalse($narrowing->filterActive);
        $this->assertEquals(Condition::orX(Condition::contains('name', 'talíř')), $narrowing->condition);
    }

    /**
     * An invalid filter narrows nothing and shows its errors, instead of quietly listing more than the
     * administrator asked for; the quick search stays outranked.
     */
    public function testInvalidFilterNarrowsNothing(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(
            ['text' => 'talíř'],
            ['groups' => [['rules' => [['filter' => 'price', 'operator' => 'contains', 'value' => 'x']]]]],
        ), [$this->createSearchableField('name')], $this->createFilters());

        $this->assertTrue($narrowing->filterActive);
        $this->assertFalse($narrowing->filterPanel?->form->isValid());
        $this->assertNull($narrowing->condition);
    }

    public function testNoFilterPanelWithoutFilters(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(null, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]));

        $this->assertNull($narrowing->filterPanel);
        $this->assertFalse($narrowing->filterActive);
        $this->assertNull($narrowing->condition);
    }

    /**
     * The form is rendered even before anything is searched, so the quick search exists without a term.
     */
    public function testQuickSearchWithoutTermNarrowsNothing(): void
    {
        $narrowing = $this->resolve(DatagridNarrowingResolverFactory::createRequestState(), [$this->createSearchableField('name')]);

        $this->assertNotNull($narrowing->quickSearch);
        $this->assertNull($narrowing->quickSearch->term);
        $this->assertNull($narrowing->condition);
    }

    public function testNothingIsBuiltOutsideRequest(): void
    {
        $scope = $this->createScope('domainId');

        $narrowing = $this->resolve(
            DatagridRequestState::outsideRequest(DatagridNarrowingResolverFactory::GRID_ID),
            [$this->createSearchableField('name')],
            $this->createFilters(),
            $scope,
        );

        $this->assertEquals(new DatagridNarrowing($scope, condition: $scope->createCondition()), $narrowing);
    }

    /**
     * A declaration that cannot work fails in a test or a command as well, not only when a page is rendered.
     */
    public function testDeclarationsAreValidatedOutsideRequest(): void
    {
        $this->expectException(FieldNotSearchableException::class);

        $this->resolve(
            DatagridRequestState::outsideRequest(DatagridNarrowingResolverFactory::GRID_ID),
            [new FieldDescriptor('computed', ['searchable' => true, 'virtual' => true])],
        );
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    private function resolve(
        DatagridRequestState $requestState,
        array $fields = [],
        ?FilterCollection $declaredFilters = null,
        ?DomainControlScope $domainControlScope = null,
    ): DatagridNarrowing {
        return DatagridNarrowingResolverFactory::create()->resolve(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            $fields,
            $declaredFilters ?? new FilterCollection(),
            $requestState,
            $domainControlScope,
        );
    }

    private function createFilters(): FilterCollection
    {
        $filters = new FilterCollection();
        $filters
            ->add(TextFilter::new('name'))
            ->add(NumericFilter::new('price'));

        return $filters;
    }

    private function createSearchableField(string $name): FieldDescriptor
    {
        return new FieldDescriptor($name, ['searchable' => true]);
    }

    private function createScope(?string $domainIdPath): DomainControlScope
    {
        return new DomainControlScope(DomainControlType::FILTER, [1, 2], 1, 'crud_test', $domainIdPath);
    }
}
