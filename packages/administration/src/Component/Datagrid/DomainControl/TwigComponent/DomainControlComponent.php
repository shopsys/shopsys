<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Renders the domain control of a datagrid — either its own domain filter or the domain switcher
 * of the whole administration.
 *
 * The scope is resolved per request, because both the available domains and the selection come from
 * the environment. Once this becomes a live component, the scope must be resolved on every render
 * as well and never dehydrated as a live property.
 */
#[AsTwigComponent(
    name: 'Admin:Grid:DomainControl',
    template: '@ShopsysAdministration/components/grid/domain_control.html.twig',
)]
final class DomainControlComponent
{
    public DomainControlScope $scope;

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
