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
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowing;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The rules of the searchable fields are covered by `SearchablePathResolverTest`; this asserts how the narrowing
 * is put together from them and from the request.
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
        ?DomainControlScope $domainControlScope = null,
    ): DatagridNarrowing {
        return DatagridNarrowingResolverFactory::create()->resolve(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            $fields,
            $requestState,
            $domainControlScope,
        );
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
