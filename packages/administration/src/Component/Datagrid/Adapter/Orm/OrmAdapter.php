<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactoryInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapter implements EntityClassAwareAdapterInterface, PathDescribingAdapterInterface
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
        private readonly DqlExpressionBuilderFactoryInterface $dqlExpressionBuilderFactory,
        private readonly PathWalkerInterface $pathWalker,
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
     * {@inheritdoc}
     */
    #[Override]
    public function describePath(string $path): PathDescription
    {
        return $this->pathWalker->walk($this->entityClass, $path)->description;
    }

    /**
     * The query built in the constructor is only a prototype — every request composes the selects and the
     * condition on its own copy, so that the same request builds the very same query and the prototype is
     * never touched by the joins and the parameters a condition needs.
     */
    #[Override]
    public function getDatasource(DatasourceRequest $request): DataSourceInterface
    {
        $proxyQuery = clone $this->proxyQuery;
        $proxyQuery->addSelect($request->identificationName);

        foreach ($request->fields as $field) {
            foreach ($field->getSelectProperties() as $selectProperty) {
                $proxyQuery->addSelect($selectProperty);
            }
        }

        if ($request->condition !== null) {
            $proxyQuery->applyExpression($this->dqlExpressionBuilderFactory->create($proxyQuery)->compile($request->condition));
        }

        $fields = $request->fields;

        return new DatagridDataSource(
            $proxyQuery->getQueryBuilder(),
            $request->identificationName,
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

        return new ProxyQuery(
            $entityClass,
            $entityManager,
            $this->pathWalker,
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );
    }
}
