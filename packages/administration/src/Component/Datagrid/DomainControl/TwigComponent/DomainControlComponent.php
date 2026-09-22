<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\TwigComponent;

use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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

    /**
     * @param int $maxDomainsRenderedAsTabs Above this many domains the row of tabs is wider than any window, so the choice is offered as a dropdown
     */
    public function __construct(
        private readonly Domain $domain,
        #[Autowire(param: 'shopsys.administration.datagrid.max_domains_rendered_as_tabs')]
        private readonly int $maxDomainsRenderedAsTabs,
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

    /**
     * Whether the domains are few enough to be offered as a row of tabs; the dropdown carries them otherwise.
     */
    public function fitsIntoTabs(): bool
    {
        return count($this->getDomainConfigs()) <= $this->maxDomainsRenderedAsTabs;
    }
}
