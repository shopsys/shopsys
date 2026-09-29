<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\DomainControl\TwigComponent;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\TwigComponent\DomainControlComponent;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

final class DomainControlComponentTest extends TestCase
{
    public function testOnlyTheDomainsOfTheScopeAreRenderedInTheirOriginalOrder(): void
    {
        $component = $this->createComponent(
            [1, 2, 3],
            new DomainControlScope(DomainControlType::FILTER, [3, 1], null, 'crud_test'),
        );

        $domainIds = array_map(
            static fn (DomainConfig $domainConfig): int => $domainConfig->getId(),
            $component->getDomainConfigs(),
        );

        $this->assertSame([1, 3], $domainIds);
    }

    public function testNoDomainIsRenderedForAnAdministratorWithoutDomains(): void
    {
        $component = $this->createComponent(
            [1, 2],
            new DomainControlScope(DomainControlType::FILTER, [], null, 'crud_test'),
        );

        $this->assertSame([], $component->getDomainConfigs());
    }

    public function testTheSwitcherRendersTheDomainsOfTheScope(): void
    {
        $component = $this->createComponent(
            [1, 2],
            new DomainControlScope(DomainControlType::SWITCHER, [1, 2], 2, null),
        );

        $this->assertCount(2, $component->getDomainConfigs());
    }

    /**
     * @param int[] $adminEnabledDomainIds
     */
    private function createComponent(array $adminEnabledDomainIds, DomainControlScope $scope): DomainControlComponent
    {
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomains')->willReturn(
            array_map(fn (int $domainId): DomainConfig => $this->createDomainConfig($domainId), $adminEnabledDomainIds),
        );

        $component = new DomainControlComponent($domainStub);
        $component->scope = $scope;

        return $component;
    }

    private function createDomainConfig(int $domainId): DomainConfig
    {
        return new DomainConfig(
            $domainId,
            'https://example.com/' . $domainId,
            'Domain ' . $domainId,
            'cs',
            new DateTimeZone('Europe/Prague'),
            'https://example.com/' . $domainId,
        );
    }
}
