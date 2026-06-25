<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Domain;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterMode;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterType;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;

final class CrudDomainFilterResolver
{
    /**
     * Alias under which the selected domain's row is joined for COLLECTION entities.
     * Controllers can reference it in configureQuery()/configureDatagrid() to read per-domain columns.
     */
    public const string DOMAIN_JOIN_ALIAS = 'crudDomainFilterDomain';

    private const string DOMAIN_ID_FIELD = 'domainId';

    private const string FILTER_PARAMETER = 'crudDomainFilterId';

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * Determines the effective filtering strategy for the given entity, honoring explicit config overrides.
     *
     * @param class-string $entityClass Resolved entity class
     */
    public function resolveType(string $entityClass, CrudConfigData $config): DomainFilterType
    {
        if ($config->isDomainFilterDisabled()) {
            return DomainFilterType::NONE;
        }

        if ($config->getDomainFilterType() !== null) {
            return $config->getDomainFilterType();
        }

        return $this->autodetectType($entityClass, $config);
    }

    public function getSelectedDomainId(string $namespace, DomainFilterMode $mode): ?int
    {
        return match ($mode) {
            DomainFilterMode::SWITCH => $this->adminDomainTabsFacade->getSelectedDomainId(),
            DomainFilterMode::FILTER => $this->adminDomainFilterTabsFacade->getSelectedDomainId($namespace),
        };
    }

    public function applyFilter(
        QueryBuilder $queryBuilder,
        DomainFilterType $type,
        ?int $domainId,
        CrudConfigData $config,
    ): void {
        if ($type === DomainFilterType::NONE || $domainId === null) {
            return;
        }

        $rootAlias = ProxyQuery::DEFAULT_ALIAS;

        if ($type === DomainFilterType::SCALAR) {
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $rootAlias, $config->getDomainFilterField(), self::FILTER_PARAMETER))
                ->setParameter(self::FILTER_PARAMETER, $domainId);

            return;
        }

        // COLLECTION entities have a domain row for every domain, so filtering by row existence would be a
        // no-op. Instead we LEFT JOIN the row of the selected domain (1:1 — rows are neither dropped nor
        // multiplied) and expose it under DOMAIN_JOIN_ALIAS so the controller can read per-domain columns
        // (e.g. visibility) in configureQuery()/configureDatagrid().
        $queryBuilder
            ->leftJoin(
                sprintf('%s.%s', $rootAlias, $config->getDomainFilterAssociation()),
                self::DOMAIN_JOIN_ALIAS,
                Join::WITH,
                sprintf('%s.%s = :%s', self::DOMAIN_JOIN_ALIAS, self::DOMAIN_ID_FIELD, self::FILTER_PARAMETER),
            )
            ->setParameter(self::FILTER_PARAMETER, $domainId);
    }

    /**
     * @param class-string $entityClass
     */
    private function autodetectType(string $entityClass, CrudConfigData $config): DomainFilterType
    {
        /** @var \Doctrine\ORM\EntityManager $manager */
        $manager = $this->managerRegistry->getManagerForClass($entityClass);
        $classMetadata = $manager->getClassMetadata($entityClass);

        if ($classMetadata->hasField($config->getDomainFilterField())) {
            return DomainFilterType::SCALAR;
        }

        $association = $config->getDomainFilterAssociation();

        if ($classMetadata->hasAssociation($association)) {
            $targetClassMetadata = $manager->getClassMetadata($classMetadata->getAssociationTargetClass($association));

            if ($targetClassMetadata->hasField(self::DOMAIN_ID_FIELD)) {
                return DomainFilterType::COLLECTION;
            }
        }

        return DomainFilterType::NONE;
    }
}
