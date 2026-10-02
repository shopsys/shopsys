<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapter implements EntityClassAwareAdapterInterface
{
    private ProxyQuery $proxyQuery;

    /**
     * @param class-string $entityClass
     * @param null|\Closure(\Doctrine\ORM\QueryBuilder $configureQuery): void $configureQuery
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
        private readonly HintsHelper $hintsHelper,
        private readonly CrudEntityIdentifierExtractor $crudEntityIdentifierExtractor,
        ?Closure $configureQuery,
    ) {
        $this->proxyQuery = $this->createProxyQuery($entityClass);

        if ($configureQuery !== null) {
            $configureQuery($this->proxyQuery->getQueryBuilder());
        }
    }

    /**
     * @return class-string
     */
    #[Override]
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param array<\Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor> $fields
     */
    #[Override]
    public function getDatasource(string $identificationName, array $fields): DataSourceInterface
    {
        $this->proxyQuery->addSelect($identificationName);

        foreach ($fields as $field) {
            foreach ($field->getSelectProperties() as $selectProperty) {
                $this->proxyQuery->addSelect($selectProperty);
            }
        }

        return new DatagridDataSource(
            $this->proxyQuery->getQueryBuilder(),
            $identificationName,
            function ($row, $results) use ($fields) {
                foreach ($fields as $field) {
                    if ($field->getTransform() === null && !$field->hasMultipleProperties()) {
                        // the grid reads the value directly from the selected property
                        continue;
                    }

                    $row[$field->getName()] = $this->computeFieldValue($field, $row, $results);
                }

                return $row;
            },
            $this->hintsHelper->getDefaultHints(),
        );
    }

    /**
     * @param mixed[] $row
     * @param mixed[][] $results
     */
    private function computeFieldValue(FieldDescriptor $field, array $row, array $results): mixed
    {
        $valuesByProperty = [];

        foreach ($field->getProperties() as $property) {
            $valuesByProperty[$property] = $row[$property] ?? null;
        }

        $value = $field->hasMultipleProperties() ? $valuesByProperty : reset($valuesByProperty);

        if ($field->getTransform() !== null) {
            return call_user_func($field->getTransform(), $value, $row, $results);
        }

        if (is_array($value) && $field->getTemplate() === null) {
            // without a template there is nothing to render the combined values with, so they are joined into one string
            return implode(' ', array_filter($value, static fn (mixed $propertyValue) => $propertyValue !== null && $propertyValue !== ''));
        }

        return $value;
    }

    /**
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        $this->crudEntityIdentifierExtractor->assertSupportedEntity($entityClass);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);

        return new ProxyQuery($entityClass, $entityManager, $this->localization->getCurrentLocaleForTranslatableEntities());
    }
}
