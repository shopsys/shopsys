<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use Closure;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;

final class DomainControlScopeTest extends TestCase
{
    public function testEffectiveDomainIdsContainOnlyTheSelectedDomain(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2, 3], 2, 'crud_test', 'domainId');

        $this->assertSame([2], $scope->getEffectiveDomainIds());
    }

    public function testEffectiveDomainIdsContainAllDomainsWithoutSelection(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 3], null, 'crud_test', 'domainId');

        $this->assertSame([1, 3], $scope->getEffectiveDomainIds());
    }

    public function testConditionIsAnInclusionOfTheEffectiveDomainIdsOnTheDomainIdPath(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2, 3], 2, 'crud_test', 'domains.domainId');

        $condition = $scope->createCondition();

        $this->assertEquals(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2]), $condition);
    }

    public function testConditionMatchesTheEffectiveDomainIdsOnTheDomainIdPath(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2, 3], 2, 'crud_test', 'domains.domainId');

        $matchesRow = $this->createMatcher($scope);

        $this->assertTrue($matchesRow(['domains' => [['domainId' => 2]]]));
        $this->assertFalse($matchesRow(['domains' => [['domainId' => 1]]]));
    }

    public function testConditionOfAnAdministratorWithoutDomainsMatchesNothing(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [], null, 'crud_test', 'domainId');

        $matchesRow = $this->createMatcher($scope);

        $this->assertFalse($matchesRow(['domainId' => 1]));
    }

    public function testEntityWithoutDomainIsNotFilteredAtAll(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2], null, 'crud_test');

        $this->assertFalse($scope->isQueryFiltered());
        $this->assertFalse($scope->isDomainWorthDisplaying());
        $this->assertNull($scope->createCondition());
    }

    public function testDomainIsNotWorthDisplayingWithSingleDomain(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1], 1, 'crud_test', 'domainId');

        $this->assertTrue($scope->isQueryFiltered());
        $this->assertFalse($scope->isDomainWorthDisplaying());
    }

    /**
     * @return \Closure(array<string, mixed> $row): bool
     */
    private function createMatcher(DomainControlScope $domainControlScope): Closure
    {
        $condition = $domainControlScope->createCondition();
        $this->assertNotNull($condition);

        $expression = (new ExpressionCompiler(new ExpressionOperatorEnum()))->compile(
            new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator()),
            $condition,
        );
        $this->assertInstanceOf(Closure::class, $expression);

        return $expression;
    }
}
