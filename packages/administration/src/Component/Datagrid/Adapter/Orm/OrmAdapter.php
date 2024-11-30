<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapter implements AdapterInterface
{
    private ProxyQuery $proxyQuery;

    /**
     * @param class-string $entityClass
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        string $entityClass,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
    ) {
        $this->proxyQuery = $this->createProxyQuery($entityClass);
    }

    /**
     * @param string $identificationName
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        $this->proxyQuery->addSelect($identificationName);

        foreach ($fields as $field) {
            if ($field->getMappingProperty() === null) {
                continue;
            }

            $this->proxyQuery->addSelect($field->getMappingProperty());
        }

        return new DatagridDataSource($this->proxyQuery->getQueryBuilder(), $identificationName, function ($row, $results) use ($fields) {
            foreach ($fields as $field) {
                if ($field->getTransform() !== null) {
                    $row[$field->getName()] = call_user_func($field->getTransform(), $row[$field->getName()], $row, $results);
                }
            }

            return $row;
        });
    }

    /**
     * @param class-string $entityClass
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery
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
