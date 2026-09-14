<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Narrowing;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowing;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * Asserts how the narrowing is put together from the domain control.
 */
final class DatagridNarrowingResolverTest extends TestCase
{
    public function testNothingNarrowsWithoutAnyCondition(): void
    {
        $narrowing = $this->resolve();

        $this->assertEquals(new DatagridNarrowing(), $narrowing);
    }

    public function testSingleConditionIsPassedAsItIs(): void
    {
        $scope = $this->createScope('domainId');

        $narrowing = $this->resolve(domainControlScope: $scope);

        $this->assertSame($scope, $narrowing->domainControlScope);
        $this->assertEquals($scope->createCondition(), $narrowing->condition);
    }

    public function testDomainControlOfEntityWithoutDomainNarrowsNothing(): void
    {
        $narrowing = $this->resolve(domainControlScope: $this->createScope(null));

        $this->assertNull($narrowing->condition);
    }

    private function resolve(?DomainControlScope $domainControlScope = null): DatagridNarrowing
    {
        return DatagridNarrowingResolverFactory::create()->resolve($domainControlScope);
    }

    private function createScope(?string $domainIdPath): DomainControlScope
    {
        return new DomainControlScope(DomainControlType::FILTER, [1, 2], 1, 'crud_test', $domainIdPath);
    }
}
