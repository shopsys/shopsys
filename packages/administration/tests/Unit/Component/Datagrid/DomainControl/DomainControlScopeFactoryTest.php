<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use stdClass;

final class DomainControlScopeFactoryTest extends TestCase
{
    public function testScopeIsNotCreatedWithoutDomainControl(): void
    {
        $scope = $this->createFactory()->create(new DomainControlConfig(DomainControlType::NONE));

        $this->assertNull($scope);
    }

    public function testFilterUsesTheConfiguredNamespace(): void
    {
        $adminDomainFilterTabsFacadeMock = $this->createMock(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->with('crud_test', [1, 2, 3])
            ->willReturn(2);

        $scope = $this
            ->createFactory([1, 2, 3], $adminDomainFilterTabsFacadeMock)
            ->create(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test'), TestScopedDomainEntity::class);

        $this->assertNotNull($scope);
        $this->assertSame(DomainControlType::FILTER, $scope->type);
        $this->assertTrue($scope->isFilter());
        $this->assertSame('crud_test', $scope->filterNamespace);
        $this->assertSame([1, 2, 3], $scope->domainIds);
        $this->assertSame(2, $scope->selectedDomainId);
        $this->assertSame('domainId', $scope->domainIdPath);
        $this->assertTrue($scope->isQueryFiltered());
    }

    public function testEntityWithoutDomainGetsNoDomainIdPath(): void
    {
        $scope = $this
            ->createFactory()
            ->create(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test'), stdClass::class);

        $this->assertNotNull($scope);
        $this->assertNull($scope->domainIdPath);
        $this->assertFalse($scope->isQueryFiltered());
    }

    public function testUnknownListedEntityGetsNoDomainIdPath(): void
    {
        $scope = $this
            ->createFactory()
            ->create(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test'));

        $this->assertNotNull($scope);
        $this->assertNull($scope->domainIdPath);
    }

    public function testConfiguredNamespaceSharesTheSelectionBetweenDatagrids(): void
    {
        $adminDomainFilterTabsFacadeMock = $this->createMock(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->with('crud_shared', [1, 2, 3])
            ->willReturn(null);

        $scope = $this
            ->createFactory([1, 2, 3], $adminDomainFilterTabsFacadeMock)
            ->create(new DomainControlConfig(DomainControlType::FILTER, null, 'crud_shared'), TestScopedDomainEntity::class);

        $this->assertNotNull($scope);
        $this->assertSame('crud_shared', $scope->filterNamespace);
        $this->assertNull($scope->selectedDomainId);
    }

    public function testDomainIdsAreLimitedByAllowedAndAdminEnabledDomains(): void
    {
        $adminDomainFilterTabsFacadeMock = $this->createMock(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->with('crud_test', [1, 3])
            ->willReturn(null);

        $scope = $this
            ->createFactory([1, 2, 3], $adminDomainFilterTabsFacadeMock)
            ->create(new DomainControlConfig(DomainControlType::FILTER, [1, 3, 4], 'crud_test'), TestScopedDomainEntity::class);

        $this->assertNotNull($scope);
        $this->assertSame([1, 3], $scope->domainIds);
    }

    public function testSwitcherUsesTheDomainSelectedInAdministration(): void
    {
        $adminDomainTabsFacadeMock = $this->createMock(AdminDomainTabsFacade::class);
        $adminDomainTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->willReturn(3);

        $scope = $this
            ->createFactory([1, 2, 3], null, $adminDomainTabsFacadeMock)
            ->create(new DomainControlConfig(DomainControlType::SWITCHER), TestScopedDomainEntity::class);

        $this->assertNotNull($scope);
        $this->assertSame(DomainControlType::SWITCHER, $scope->type);
        $this->assertFalse($scope->isFilter());
        $this->assertSame(3, $scope->selectedDomainId);
        $this->assertNull($scope->filterNamespace);
    }

    /**
     * @param int[] $adminEnabledDomainIds
     */
    private function createFactory(
        array $adminEnabledDomainIds = [1, 2, 3],
        ?AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade = null,
        ?AdminDomainTabsFacade $adminDomainTabsFacade = null,
    ): DomainControlScopeFactory {
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn($adminEnabledDomainIds);

        return new DomainControlScopeFactory(
            $adminDomainFilterTabsFacade ?? $this->createStub(AdminDomainFilterTabsFacade::class),
            $adminDomainTabsFacade ?? $this->createStub(AdminDomainTabsFacade::class),
            $domainStub,
        );
    }
}

final class TestScopedDomainEntity implements DomainSeparatedEntityInterface
{
    #[Override]
    public function getDomainId(): int
    {
        return 1;
    }
}
