<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition\DqlCondition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\DatagridDataSource;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompilerInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Doctrine\NormalizedFunction;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\HintsHelper;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;

final class OrmAdapterTest extends TestCase
{
    public function testQueryBuiltInTheConstructorIsOnlyPrototype(): void
    {
        $ormAdapter = $this->createOrmAdapter();
        $request = new DatasourceRequest('id', [new FieldDescriptor('name')], Condition::equals('id', 1));

        $firstDql = $this->getDql($ormAdapter->getDatasource($request));
        $secondDql = $this->getDql($ormAdapter->getDatasource($request));

        $this->assertSame($firstDql, $secondDql);
        $this->assertSame(1, substr_count($firstDql, 'o.id = :dgexpr_0'));
    }

    public function testDatasourceIsNotNarrowedWithoutCondition(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $this->assertStringNotContainsString('WHERE', $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', []))));
    }

    /**
     * The adapter keeps no state between the requests — a condition of one listing never leaks into another.
     */
    public function testEveryRequestIsAnsweredOnItsOwn(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $narrowedDql = $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1))));
        $plainDql = $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', [])));

        $this->assertStringContainsString('WHERE', $narrowedDql);
        $this->assertStringNotContainsString('WHERE', $plainDql);
    }

    public function testComposedConditionIsCompiledIntoOneWhereClause(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $dql = $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::andX(
            Condition::equals('id', 1),
            Condition::contains('name', 'Hrnek'),
        ))));

        $this->assertStringContainsString('WHERE o.id = :dgexpr_0 AND NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_1)', $dql);
    }

    /**
     * The join a condition needs is added to the copy the data source is built from, so a field of the same
     * path is selected through the very same join.
     */
    public function testConditionSharesItsJoinWithSelectedField(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $dql = $this->getDql($ormAdapter->getDatasource(new DatasourceRequest(
            'id',
            [new FieldDescriptor('currency.code')],
            Condition::equals('currency.code', 'CZK'),
        )));

        $this->assertSame(1, substr_count($dql, 'LEFT JOIN'));
        $this->assertStringContainsString('currency_join.code AS currency__code', $dql);
        $this->assertStringContainsString('currency_join.code = :dgexpr_0', $dql);
    }

    /**
     * A declaration asks the adapter before anything is built, so the answer must not need a query.
     */
    public function testCapabilitiesAnswerForTheMediumWithoutBuildingAnything(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $this->assertTrue($ormAdapter->getExpressionCapabilities()->supportsOperator(ExpressionOperatorEnum::BETWEEN));
        $this->assertTrue($ormAdapter->getExpressionCapabilities()->supportsOperator(ExpressionOperatorEnum::IS_EMPTY));
        $this->assertFalse($ormAdapter->getExpressionCapabilities()->supportsOperator('somethingElse'));
    }

    public function testPathIsDescribedWithoutTouchingThePrototypeQuery(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $pathDescription = $ormAdapter->describePath('currency.code');

        $this->assertSame(PathCardinalityEnum::TO_ONE, $pathDescription->cardinality);
        $this->assertSame(PathValueTypeEnum::STRING, $pathDescription->valueType);
        // describing must not join the association into every query built afterwards
        $this->assertStringNotContainsString('JOIN', $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', []))));
    }

    /**
     * What the vocabulary cannot say is said in DQL directly, composed with the rest of the condition
     * and compiled on the very same copy of the query.
     */
    public function testDqlConditionIsCompiledTogetherWithTheVocabulary(): void
    {
        $ormAdapter = $this->createOrmAdapter();

        $dql = $this->getDql($ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::andX(
            Condition::equals('id', 1),
            new DqlCondition(
                static fn (QueryBuilder $queryBuilder, ProxyQuery $proxyQuery): string => sprintf('SIZE(o.tags) > :%s', $proxyQuery->addParameter(3)),
            ),
        ))));

        $this->assertStringContainsString('WHERE o.id = :dgexpr_0 AND SIZE(o.tags) > :dgexpr_1', $dql);
    }

    public function testExpressionOfAnotherMediumIsRefused(): void
    {
        $expressionCompilerStub = $this->createStub(ExpressionCompilerInterface::class);
        $expressionCompilerStub->method('compile')->willReturn(new DateTimeImmutable());
        $ormAdapter = $this->createOrmAdapter($expressionCompilerStub);

        $this->expectException(ExpressionFromForeignMediumException::class);

        $ormAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1)));
    }

    private function getDql(DataSourceInterface $dataSource): string
    {
        $this->assertInstanceOf(DatagridDataSource::class, $dataSource);

        $reflectionProperty = new ReflectionProperty($dataSource, 'queryBuilder');
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $reflectionProperty->getValue($dataSource);

        return $queryBuilder->getDQL();
    }

    private function createOrmAdapter(?ExpressionCompilerInterface $expressionCompiler = null): OrmAdapter
    {
        $entityManager = $this->createEntityManager();
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
            new DqlExpressionBuilderFactory(new ExpressionOperatorApplicability()),
            $expressionCompiler ?? new ExpressionCompiler(new ExpressionOperatorEnum()),
            null,
        );
    }

    private function createEntityManager(): EntityManagerInterface
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/Fixture'], true);
        $configuration->addCustomStringFunction('NORMALIZED', NormalizedFunction::class);

        // the server version is pinned so that the PostgreSQL platform is resolved without connecting to a database
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_pgsql',
                'serverVersion' => '16',
            ],
            $configuration,
        );

        return new EntityManager($connection, $configuration);
    }
}
