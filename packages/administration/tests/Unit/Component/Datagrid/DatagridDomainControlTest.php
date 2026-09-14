<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Closure;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridDomainControlTest extends TestCase
{
    /**
     * The label of the automatically added domain field is translated right away.
     */
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);

        Translator::injectSelf($translatorStub);
    }

    public function testDomainFieldIsAddedWhenTheDomainIsWorthDisplaying(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));

        $datagrid->createView();

        $domainField = $adapter->getLastRequestFields()['domainId'] ?? null;
        $this->assertNotNull($domainField);
        $this->assertSame('@ShopsysAdministration/datagrid/cell/domain_icon.html.twig', $domainField->getTemplate());
        $this->assertSame('domainId', $domainField->getMappingProperty());
    }

    public function testDomainFieldIsLastByDefault(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));
        $datagrid->add('name');

        $datagrid->createView();

        $this->assertSame(['name', 'domainId'], array_keys($adapter->getLastRequestFields()));
    }

    public function testDomainFieldIsNotAddedForSingleDomain(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1], 1));
        $datagrid->add('name');

        $datagrid->createView();

        $this->assertArrayNotHasKey('domainId', $adapter->getLastRequestFields());
    }

    public function testDomainFieldIsNotAddedWithoutDomainControl(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, null);
        $datagrid->add('name');

        $datagrid->createView();

        $this->assertArrayNotHasKey('domainId', $adapter->getLastRequestFields());
    }

    public function testDomainFieldIsNotAddedForEntityWithoutDomain(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2, null));
        $datagrid->add('name');

        $datagrid->createView();

        $this->assertArrayNotHasKey('domainId', $adapter->getLastRequestFields());
    }

    public function testManuallyAddedDomainFieldTakesPrecedence(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));
        $datagrid->add('domainId', [
            'label' => 'Custom domain',
        ]);
        $datagrid->add('name');

        $datagrid->createView();

        $fields = $adapter->getLastRequestFields();
        $this->assertSame(['domainId', 'name'], array_keys($fields));
        $this->assertSame('Custom domain', $fields['domainId']->getLabel());
        $this->assertNull($fields['domainId']->getTemplate());
    }

    public function testDomainConditionLimitsTheDatagridToTheSelectedDomain(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));

        $datagrid->createView();

        $matchesRow = $this->getLastRequestMatcher($adapter);
        $this->assertTrue($matchesRow(['domainId' => 2]));
        $this->assertFalse($matchesRow(['domainId' => 1]));
    }

    public function testDomainConditionUsesTheDomainIdPathOfTheScope(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2, 'domains.domainId'));

        $datagrid->createView();

        $matchesRow = $this->getLastRequestMatcher($adapter);
        $this->assertTrue($matchesRow(['domains' => [['domainId' => 2]]]));
        $this->assertFalse($matchesRow(['domains' => [['domainId' => 1]]]));
    }

    public function testDomainConditionCoversAllAvailableDomainsWithoutSelection(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 3], null));

        $datagrid->createView();

        $matchesRow = $this->getLastRequestMatcher($adapter);
        $this->assertTrue($matchesRow(['domainId' => 1]));
        $this->assertTrue($matchesRow(['domainId' => 3]));
        $this->assertFalse($matchesRow(['domainId' => 2]));
    }

    public function testDomainConditionIsAppliedEvenOnSingleDomain(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1], 1));

        $datagrid->createView();

        $matchesRow = $this->getLastRequestMatcher($adapter);
        $this->assertTrue($matchesRow(['domainId' => 1]));
        $this->assertFalse($matchesRow(['domainId' => 2]));
    }

    public function testRemovingTheDomainFieldKeepsTheDomainCondition(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));
        $datagrid->add('name');
        $datagrid->add('domainId', [
            'visible' => false,
        ]);

        $datagrid->createView();

        $this->assertTrue($this->getLastRequestMatcher($adapter)(['domainId' => 2]));
    }

    /**
     * Rendering the datagrid twice sends the very same request twice — the domain condition is a part of
     * every request instead of a state of the adapter, and the domain field is added only once.
     */
    public function testEveryViewSendsTheSameRequest(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2));

        $datagrid->createView();
        $datagrid->createView();

        $this->assertCount(2, $adapter->requests);
        $this->assertEquals($adapter->requests[0], $adapter->requests[1]);
        $this->assertNotNull($adapter->requests[1]->condition);
        $this->assertCount(1, $adapter->getLastRequestFields());
    }

    public function testNoDomainConditionWithoutDomainControl(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, null);

        $datagrid->createView();

        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testNoDomainConditionForEntityWithoutDomain(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, $this->createScope([1, 2, 3], 2, null));

        $datagrid->createView();

        $this->assertNull($adapter->getLastRequest()->condition);
    }

    /**
     * The condition of the last request compiled for data in memory, so that the test asserts on what
     * it matches rather than on its shape.
     *
     * @return \Closure(array<string, mixed> $row): bool
     */
    private function getLastRequestMatcher(CapturingAdapter $adapter): Closure
    {
        $condition = $adapter->getLastRequest()->condition;
        $this->assertNotNull($condition);

        $expression = (new ExpressionCompiler(new ExpressionOperatorEnum()))->compile(
            $adapter->getExpressionCapabilities(),
            $condition,
        );
        $this->assertInstanceOf(Closure::class, $expression);

        return $expression;
    }

    /**
     * @param int[] $domainIds
     */
    private function createScope(
        array $domainIds,
        ?int $selectedDomainId,
        ?string $domainIdPath = 'domainId',
    ): DomainControlScope {
        return new DomainControlScope(DomainControlType::FILTER, $domainIds, $selectedDomainId, 'crud_test', $domainIdPath);
    }

    private function createDatagrid(CapturingAdapter $adapter, ?DomainControlScope $scope): Datagrid
    {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        return new Datagrid($adapter, $gridFactoryStub, [
            'roleConstant' => 'ROLE_CRUD_TEST',
            'domainControlScope' => $scope,
        ]);
    }
}
