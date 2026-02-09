<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapter implements AdapterInterface
{
    private ProxyQuery $proxyQuery;

    /**
     * @param class-string $entityClass
     * @param null|\Closure(\Doctrine\ORM\QueryBuilder $configureQuery): void $configureQuery
     */
    public function __construct(
        string $entityClass,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
        private readonly HintsHelper $hintsHelper,
        ?Closure $configureQuery,
    ) {
        $this->proxyQuery = $this->createProxyQuery($entityClass);

        if ($configureQuery !== null) {
            $configureQuery($this->proxyQuery->getQueryBuilder());
        }
    }

    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    #[Override]
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        $this->proxyQuery->addSelect($identificationName);

        foreach ($fields as $field) {
            if ($field->getSelectProperty() === null) {
                continue;
            }

            $this->proxyQuery->addSelect($field->getSelectProperty());
        }

        return new DatagridDataSource(
            $this->proxyQuery->getQueryBuilder(),
            $identificationName,
            function ($row, $results) use ($fields) {
                foreach ($fields as $field) {
                    if ($field->getTransform() !== null) {
                        $row[$field->getName()] = call_user_func($field->getTransform(), $row[$field->getName()] ?? null, $row, $results);
                    }
                }

                return $row;
            },
            $this->hintsHelper->getDefaultHints(),
        );
    }

    /**
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);
        $classMetadata = $entityManager->getClassMetadata($entityClass);

        if (count($classMetadata->getIdentifierFieldNames()) !== 1) {
            throw new RuntimeException('Crud controller does not support entities with composite primary keys.');
        }

        return new ProxyQuery($entityClass, $entityManager, $this->localization->getCurrentLocaleForTranslatableEntities());
    }
}
