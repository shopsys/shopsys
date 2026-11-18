<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition\DqlCondition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\DatagridDataSource;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactoryInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;

final class OrmAdapterTest extends TestCase
{
    use QueryAssertionTrait;

    public function testTheSameRequestBuildsTheSameQuery(): void
    {
        $ormAdapter = $this->createOrmAdapter();
        $request = new DatasourceRequest('id', [new FieldDescriptor('name')], Condition::equals('id', 1));

        $firstDataSource = $ormAdapter->getDatasource($request);
        $secondDataSource = $ormAdapter->getDatasource($request);

        $expectedDql = 'SELECT o.id AS id, o.name AS name FROM ' . DummyProduct::class . ' o WHERE o.id = :dgexpr_0';
        $this->assertQuery($expectedDql, $this->getQueryBuilder($firstDataSource));
        $this->assertQuery($expectedDql, $this->getQueryBuilder($secondDataSource));
    }

    public function testEveryRequestIsAnsweredOnItsOwn(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $narrowedDataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1)));
        $plainDataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], null));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE o.id = :dgexpr_0',
            $this->getQueryBuilder($narrowedDataSource),
        );
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o', $this->getQueryBuilder($plainDataSource));
    }

    public function testConditionSharesItsJoinWithSelectedField(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $dataSource = $ormAdapter->getDatasource(new DatasourceRequest(
            'id',
            [new FieldDescriptor('currency.code')],
            Condition::equals('currency.code', 'CZK'),
        ));

        $this->assertQuery(
            'SELECT o.id AS id, currency_join.code AS currency__code FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' WHERE currency_join.code = :dgexpr_0',
            $this->getQueryBuilder($dataSource),
        );
    }

    public function testQueryIsConfiguredByConfigureQuery(): void
    {
        $ormAdapter = $this->createOrmAdapter(configureQuery: static function (QueryBuilder $queryBuilder): void {
            $queryBuilder->andWhere('o.isActive = true');
        });

        $dataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], null));

        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.isActive = true', $this->getQueryBuilder($dataSource));
    }

    public function testPathIsDescribedWithoutTouchingThePrototypeQuery(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $pathDescription = $ormAdapter->describePath('currency.code');
        $dataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], null));

        $this->assertSame(PathCardinalityEnum::TO_ONE, $pathDescription->cardinality);
        $this->assertSame(PathValueTypeEnum::STRING, $pathDescription->valueType);
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o', $this->getQueryBuilder($dataSource));
    }

    public function testDqlConditionIsCompiledTogetherWithTheVocabulary(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $dataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::andX(
            Condition::equals('id', 1),
            new DqlCondition(
                static fn (ProxyQuery $proxyQuery): string => sprintf('SIZE(%s.tags) > :%s', ProxyQuery::DEFAULT_ALIAS, $proxyQuery->addParameter(3)),
            ),
        )));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE o.id = :dgexpr_0 AND SIZE(o.tags) > :dgexpr_1',
            $this->getQueryBuilder($dataSource),
        );
    }

    public function testExpressionsAreBuiltByTheGivenFactory(): void
    {
        $expressionBuilderStub = $this->createStub(DqlExpressionBuilder::class);
        $expressionBuilderStub->method('compile')->willReturn(new Andx(['o.id = 42']));
        $expressionBuilderFactoryStub = $this->createStub(DqlExpressionBuilderFactoryInterface::class);
        $expressionBuilderFactoryStub->method('create')->willReturn($expressionBuilderStub);

        $ormAdapter = $this->createOrmAdapter($expressionBuilderFactoryStub);
        $dataSource = $ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1)));

        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.id = 42', $this->getQueryBuilder($dataSource));
    }

    private function getQueryBuilder(DataSourceInterface $dataSource): QueryBuilder
    {
        $this->assertInstanceOf(DatagridDataSource::class, $dataSource);

        $reflectionProperty = new ReflectionProperty($dataSource, 'queryBuilder');
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $reflectionProperty->getValue($dataSource);

        return $queryBuilder;
    }

    private function createOrmAdapter(
        ?DqlExpressionBuilderFactoryInterface $dqlExpressionBuilderFactory = null,
        ?Closure $configureQuery = null,
    ): OrmAdapter {
        $entityManager = DummyEntityManagerFactory::create();
        $managerRegistryStub = $this->createStub(ManagerRegistry::class);
        $managerRegistryStub->method('getManagerForClass')->willReturn($entityManager);
        $localizationStub = $this->createStub(Localization::class);
        $localizationStub->method('getCurrentLocaleForTranslatableEntities')->willReturn('cs');

        return new OrmAdapter(
            DummyProduct::class,
            $managerRegistryStub,
            $localizationStub,
            $this->createStub(HintsHelper::class),
            new CrudEntityIdentifierExtractor($managerRegistryStub),
            $dqlExpressionBuilderFactory ?? new DqlExpressionBuilderFactory(new ExpressionOperatorEnum()),
            new PathWalker($entityManager),
            $configureQuery,
        );
    }
}
