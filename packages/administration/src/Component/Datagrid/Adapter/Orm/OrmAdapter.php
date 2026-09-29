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
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCapabilitiesInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompilerInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Stringable;

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
        private readonly ExpressionCompilerInterface $expressionCompiler,
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
    public function getExpressionCapabilities(): ExpressionCapabilitiesInterface
    {
        return $this->dqlExpressionBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function describePath(string $path): PathDescription
    {
        return $this->proxyQuery->describePath($path);
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
            if ($field->getSelectProperty() === null) {
                continue;
            }

            $proxyQuery->addSelect($field->getSelectProperty());
        }

        if ($request->condition !== null) {
            $proxyQuery->applyExpression($this->compileCondition($proxyQuery, $request));
        }

        $fields = $request->fields;

        return new DatagridDataSource(
            $proxyQuery->getQueryBuilder(),
            $request->identificationName,
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

    private function compileCondition(ProxyQuery $proxyQuery, DatasourceRequest $request): Stringable
    {
        $expression = $this->expressionCompiler->compile(
            $this->dqlExpressionBuilderFactory->create($proxyQuery),
            $request->condition,
        );

        if ($expression instanceof Stringable === false) {
            throw new ExpressionFromForeignMediumException(Stringable::class, $expression, self::class);
        }

        return $expression;
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
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );
    }
}
