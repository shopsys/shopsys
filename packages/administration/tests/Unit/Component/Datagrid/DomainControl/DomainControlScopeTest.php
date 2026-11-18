<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
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

    public function testConditionOfAnAdministratorWithoutDomainsMatchesNothing(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [], null, 'crud_test', 'domainId');

        $this->assertEquals(Condition::in('domainId', []), $scope->createCondition());
    }

    public function testEntityWithoutDomainIsNotFilteredAtAll(): void
    {
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2], null, 'crud_test');

        $this->assertNull($scope->createCondition());
    }
}
