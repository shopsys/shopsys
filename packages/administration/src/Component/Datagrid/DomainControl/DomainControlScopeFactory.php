<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;

final readonly class DomainControlScopeFactory
{
    /**
     * The field every entity implementing `DomainSeparatedEntityInterface` maps its domain by.
     */
    private const string DOMAIN_ID_PATH = 'domainId';

    public function __construct(
        private AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
        private Domain $domain,
    ) {
    }

    /**
     * Returns null when the datagrid displays no domain control.
     *
     * @param class-string|null $listedEntityClass Decides how the datagrid relates to a domain
     */
    public function create(DomainControlConfig $config, ?string $listedEntityClass = null): ?DomainControlScope
    {
        if ($config->isEnabled() === false) {
            return null;
        }

        $domainIds = $this->getDomainIds($config->allowedDomainIds);
        $domainIdPath = $this->getDomainIdPath($listedEntityClass);

        if ($config->type === DomainControlType::SWITCHER) {
            return new DomainControlScope(
                $config->type,
                $domainIds,
                $this->adminDomainTabsFacade->getSelectedDomainId(),
                null,
                $domainIdPath,
            );
        }

        return new DomainControlScope(
            $config->type,
            $domainIds,
            $this->adminDomainFilterTabsFacade->getSelectedDomainId($config->filterNamespace, $domainIds),
            $config->filterNamespace,
            $domainIdPath,
        );
    }

    /**
     * @param class-string|null $listedEntityClass
     */
    private function getDomainIdPath(?string $listedEntityClass): ?string
    {
        if ($listedEntityClass === null || !is_a($listedEntityClass, DomainSeparatedEntityInterface::class, true)) {
            return null;
        }

        return self::DOMAIN_ID_PATH;
    }

    /**
     * @param int[]|null $allowedDomainIds
     * @return int[]
     */
    private function getDomainIds(?array $allowedDomainIds): array
    {
        $adminEnabledDomainIds = $this->domain->getAdminEnabledDomainIds();

        if ($allowedDomainIds === null) {
            return $adminEnabledDomainIds;
        }

        return array_values(array_intersect($adminEnabledDomainIds, $allowedDomainIds));
    }
}
