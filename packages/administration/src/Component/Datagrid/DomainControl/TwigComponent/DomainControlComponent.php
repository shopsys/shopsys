<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the domain control of a datagrid, either its own domain filter or the domain switcher
 * of the whole administration.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:DomainControl',
    template: '@ShopsysAdministration/components/grid/domain_control.html.twig',
)]
final class DomainControlComponent
{
    public DomainControlScope $scope;

    /**
     * The domain filter as a compact select (next to the search of the list) instead of the row of tabs.
     */
    public bool $select = false;

    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getDomainConfigs(): array
    {
        return array_values(array_filter(
            $this->domain->getAdminEnabledDomains(),
            fn (DomainConfig $domainConfig): bool => in_array($domainConfig->getId(), $this->scope->domainIds, true),
        ));
    }
}
