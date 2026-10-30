<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\State;

use LogicException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateFactory;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

/**
 * The rules of the searchable fields and of the offered filters are covered by `SearchablePathResolverTest` and
 * `FilterSetResolverTest`; this asserts how the subscribers of the features put the state together from them and
 * from the request, and how the factory combines the conditions of the parts.
 */
final class DatagridStateFactoryTest extends TestCase
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
        $state = $this->createState(DatagridStateFactoryFixture::createRequest());

        $this->assertNull($state->condition);
        $this->assertFalse($state->isNarrowedByAdministrator());
        $this->assertFalse($state->isInvalid());
        $this->assertNull($state->get(QuickSearch::class));
        $this->assertNull($state->get(FilterPanel::class));
        $this->assertNull($state->get(DomainControlScope::class));
    }

    public function testSingleConditionIsPassedAsItIs(): void
    {
        $scope = $this->createScope('domainId');

        $state = $this->createState(DatagridStateFactoryFixture::createRequest(), domainControlScope: $scope);

        $this->assertSame($scope, $state->get(DomainControlScope::class));
        $this->assertEquals($scope->createCondition(), $state->condition);
        $this->assertFalse($state->isNarrowedByAdministrator(), 'The domain control is the scope of the listing rather than a narrowing of it.');
    }

    public function testDomainControlOfEntityWithoutDomainNarrowsNothing(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(), domainControlScope: $this->createScope(null));

        $this->assertNull($state->condition);
    }

    public function testDomainAndSearchAreCombinedByConjunction(): void
    {
        $scope = $this->createScope('domainId');

        $state = $this->createState(DatagridStateFactoryFixture::createRequest(['text' => 'hrnek']), [$this->createSearchableField('name')], domainControlScope: $scope);

        $this->assertTrue($state->isNarrowedByAdministrator());
        $this->assertEquals(
            Condition::andX($scope->createCondition(), Condition::orX(Condition::contains('name', 'hrnek'))),
            $state->condition,
        );
    }

    public function testComposedRulesNarrowTheRecords(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(null, ['operator' => 'and', 'groups' => [
            ['operator' => 'or', 'rules' => [
                ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                ['filter' => 'price', 'operator' => 'greaterThan', 'value' => '100'],
            ]],
        ]]), declaredFilters: $this->createFilters());

        $this->assertTrue($state->isNarrowedByAdministrator());
        $this->assertSame('Product_filter', $state->get(FilterPanel::class)?->form->getName());
        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100.0)),
            $state->condition,
        );
    }

    /**
     * The filter and the quick search are two ways of asking the same question, so a composed filter wins and the
     * quick search is not applied, while its form still shows what was searched for.
     */
    public function testComposedFilterOutranksTheQuickSearch(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(
            ['text' => 'talíř'],
            ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'equals', 'value' => 'Hrnek']]]]],
        ), [$this->createSearchableField('name')], $this->createFilters());

        $this->assertTrue($state->get(FilterPanel::class)?->isActive());
        $this->assertSame('talíř', $state->get(QuickSearch::class)?->term);
        $this->assertEquals(Condition::equals('name', 'Hrnek'), $state->condition);
    }

    public function testQuickSearchAppliesWhenNoRuleIsComposed(): void
    {
        $state = $this->createState(
            DatagridStateFactoryFixture::createRequest(['text' => 'talíř'], ['groups' => []]),
            [$this->createSearchableField('name')],
            $this->createFilters(),
        );

        $this->assertFalse($state->get(FilterPanel::class)?->isActive());
        $this->assertEquals(Condition::orX(Condition::contains('name', 'talíř')), $state->condition);
    }

    /**
     * An invalid filter narrows nothing and shows its errors, instead of quietly listing more than the
     * administrator asked for; the quick search stays outranked.
     */
    public function testInvalidFilterNarrowsNothing(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(
            ['text' => 'talíř'],
            ['groups' => [['rules' => [['filter' => 'price', 'operator' => 'contains', 'value' => 'x']]]]],
        ), [$this->createSearchableField('name')], $this->createFilters());

        $this->assertTrue($state->isInvalid());
        $this->assertTrue($state->isNarrowedByAdministrator());
        $this->assertFalse($state->get(FilterPanel::class)?->form->isValid());
        $this->assertNull($state->condition);
    }

    public function testNoFilterWithoutFilters(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(null, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]));

        $this->assertNull($state->get(FilterPanel::class));
        $this->assertFalse($state->isNarrowedByAdministrator());
        $this->assertNull($state->condition);
    }

    /**
     * The form is rendered even before anything is searched, so the quick search exists without a term.
     */
    public function testQuickSearchWithoutTermNarrowsNothing(): void
    {
        $state = $this->createState(DatagridStateFactoryFixture::createRequest(), [$this->createSearchableField('name')]);

        $quickSearch = $state->get(QuickSearch::class);
        $this->assertNotNull($quickSearch);
        $this->assertSame('Product_search', $quickSearch->formName);
        $this->assertNull($quickSearch->term);
        $this->assertNull($state->condition);
    }

    public function testFormsOfAnotherDatagridAreNotRead(): void
    {
        $state = $this->createState(
            new Request([
                'Order_search' => ['text' => 'hrnek'],
                'Order_filter' => ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]]],
            ]),
            [$this->createSearchableField('name')],
            $this->createFilters(),
        );

        $this->assertNull($state->get(QuickSearch::class)?->term);
        $this->assertFalse($state->get(FilterPanel::class)?->isActive());
        $this->assertNull($state->condition);
    }

    public function testNoFormIsBuiltOutsideRequest(): void
    {
        $scope = $this->createScope('domainId');

        $state = $this->createState(null, [$this->createSearchableField('name')], $this->createFilters(), $scope);

        $this->assertNull($state->get(QuickSearch::class));
        $this->assertNull($state->get(FilterPanel::class));
        $this->assertSame($scope, $state->get(DomainControlScope::class));
        $this->assertEquals($scope->createCondition(), $state->condition);
    }

    /**
     * A declaration that cannot work fails in a test or a command as well, not only when a page is rendered.
     */
    public function testDeclarationsAreValidatedOutsideRequest(): void
    {
        $this->expectException(FieldNotSearchableException::class);

        $this->createState(null, [new FieldDescriptor('computed', ['searchable' => true, 'virtual' => true])]);
    }

    /**
     * A search a project adds by a subscriber of its own applies after the ranked ones, and the scopes always apply.
     */
    public function testSearchNotRankedAppliesWhenNoRankedOneIsActive(): void
    {
        $customSearch = $this->createCustomSearch();
        $scope = $this->createScope('domainId');

        $state = $this->createStateFromParts([new QuickSearch('Product_search', null, ['name' => 'Name']), $customSearch, $scope]);

        $this->assertSame($customSearch, $state->get($customSearch::class));
        $this->assertSame($customSearch, $state->getAppliedSearch());
        $this->assertEquals(Condition::andX($scope->createCondition(), Condition::equals('saved', true)), $state->condition);
    }

    public function testRankedSearchOutranksTheSearchNotRanked(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', ['name' => 'Name']);

        $state = $this->createStateFromParts([$this->createCustomSearch(), $quickSearch]);

        $this->assertSame($quickSearch, $state->getAppliedSearch());
        $this->assertEquals($quickSearch->createCondition(), $state->condition);
    }

    /**
     * The domain control limits what the administrator may list at all, so a state without it is refused rather than
     * listing the records of every domain.
     */
    public function testDatagridWithDomainControlIsNotListedWithoutIt(): void
    {
        $config = new DatagridConfig(
            DatagridStateFactoryFixture::GRID_ID,
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            [],
            new FilterCollection(),
            new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test'),
        );

        $this->expectException(LogicException::class);

        new DatagridStateFactory(new EventDispatcher(), new RequestStack())->create($config);
    }

    /**
     * A search a project would add — filters saved by the administrator, for one.
     */
    private function createCustomSearch(): DatagridSearchInterface
    {
        return new class() implements DatagridSearchInterface {
            #[Override]
            public function isActive(): bool
            {
                return true;
            }

            #[Override]
            public function createCondition(): ConditionInterface
            {
                return Condition::equals('saved', true);
            }
        };
    }

    /**
     * @param object[] $parts
     */
    private function createStateFromParts(array $parts): DatagridState
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(DatagridStateEvent::class, static function (DatagridStateEvent $event) use ($parts): void {
            foreach ($parts as $part) {
                $event->addPart($part);
            }
        });
        $config = new DatagridConfig(DatagridStateFactoryFixture::GRID_ID, new CapturingAdapter($this->createStub(DataSourceInterface::class)), [], new FilterCollection());

        return new DatagridStateFactory($eventDispatcher, new RequestStack())->create($config);
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    private function createState(
        ?Request $request,
        array $fields = [],
        ?FilterCollection $declaredFilters = null,
        ?DomainControlScope $domainControlScope = null,
    ): DatagridState {
        $config = new DatagridConfig(
            DatagridStateFactoryFixture::GRID_ID,
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            $fields,
            $declaredFilters ?? new FilterCollection(),
        );

        return DatagridStateFactoryFixture::createState($config, $request, $domainControlScope);
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
