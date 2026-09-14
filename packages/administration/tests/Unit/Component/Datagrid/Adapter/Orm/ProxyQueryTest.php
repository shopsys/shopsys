<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ToManySelectNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Doctrine\NormalizedFunction;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticle;

final class ProxyQueryTest extends TestCase
{
    private const string LOCALE = 'cs';

    public function testRootFieldIsSelectedOnTheRootAlias(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('name');

        $this->assertSame('SELECT o.name AS name FROM ' . DummyProduct::class . ' o', $proxyQuery->getQueryBuilder()->getDQL());
    }

    public function testAssociationFieldIsJoinedAndAliasedByDotNotation(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.code');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $dql);
        $this->assertStringContainsString('currency_join.code AS currency__code', $dql);
    }

    public function testAssociationEntityItselfCanBeSelected(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $dql);
        $this->assertStringContainsString('currency_join AS currency', $dql);
    }

    public function testPathIsResolvedThroughMultipleJoins(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.format.pattern');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $dql);
        $this->assertStringContainsString('LEFT JOIN currency_join.format currency__format_join', $dql);
        $this->assertStringContainsString('currency__format_join.pattern AS currency__format__pattern', $dql);
    }

    public function testTwoPathsToTheSameAssociationDoNotShareJoinAlias(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.format.pattern');
        $proxyQuery->addSelect('secondaryCurrency.format.pattern');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('LEFT JOIN currency_join.format currency__format_join', $dql);
        $this->assertStringContainsString('LEFT JOIN secondaryCurrency_join.format secondaryCurrency__format_join', $dql);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testTheSameSelectIsAddedOnlyOnce(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.code');
        $proxyQuery->addSelect('currency.code');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertSame(1, substr_count($dql, 'currency_join.code AS currency__code'));
        $this->assertSame(1, substr_count($dql, 'LEFT JOIN'));
    }

    public function testJoinIsSharedByTwoPathsLeadingThroughIt(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.code');
        $proxyQuery->addSelect('currency.format.pattern');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertSame(1, substr_count($dql, 'LEFT JOIN o.currency currency_join'));
        $this->assertSame(2, substr_count($dql, 'LEFT JOIN'));
    }

    public function testAssociationIsJoinedRegardlessOfHowItsIdentifierIsNamed(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('warehouse.name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN o.warehouse warehouse_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testIdentifierOfAssociationIsSelectedWithoutJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.id');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertStringContainsString('IDENTITY(o.currency) AS currency__id', $dql);
        $this->assertStringNotContainsString('LEFT JOIN', $dql);
    }

    public function testTranslatedFieldIsJoinedWithCurrentLocale(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->addSelect('name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale', $dql);
        $this->assertStringContainsString('o_tr.name AS name', $dql);
        $this->assertSame(self::LOCALE, $queryBuilder->getParameter('o_tr_locale')->getValue());
    }

    public function testTwoTranslatedFieldsShareTheSingleTranslationJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->addSelect('name');
        $proxyQuery->addSelect('description');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertSame(1, substr_count($dql, 'LEFT JOIN o.translations o_tr'));
        $this->assertStringContainsString('o_tr.name AS name', $dql);
        $this->assertStringContainsString('o_tr.description AS description', $dql);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testTranslatedFieldCanBeAddressedExplicitly(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->addSelect('translations.name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale', $dql);
        $this->assertStringContainsString('o_tr.name AS translations__name', $dql);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testFieldOfTheEntityShadowsTheTranslatedOneOfTheSameName(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyShadowedArticle::class);

        $ownFieldResolution = $proxyQuery->resolvePath('name');
        $translatedFieldResolution = $proxyQuery->resolvePath('translations.name');

        $this->assertSame('o.name', $ownFieldResolution->dqlExpression);
        $this->assertSame('o_tr.name', $translatedFieldResolution->dqlExpression);
    }

    public function testExplicitTranslationsPathSharesTheJoinWithTheImplicitOne(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->addSelect('name');
        $proxyQuery->addSelect('translations.description');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(1, substr_count($queryBuilder->getDQL(), 'LEFT JOIN o.translations o_tr'));
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testSelectOfToManyPathIsNotSupported(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(ToManySelectNotSupportedException::class);
        $this->expectExceptionMessage('Path "domains.domainId" leads through a to-many association and cannot be selected');

        $proxyQuery->addSelect('domains.domainId');
    }

    public function testUnknownFieldIsNotSupported(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('Path "unknownField" cannot be resolved, entity "' . DummyProduct::class . '" has no field or association "unknownField".');

        $proxyQuery->addSelect('unknownField');
    }

    public function testCloningKeepsTheOriginalQueryBuilderUntouched(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('name');

        $clonedProxyQuery = clone $proxyQuery;
        $clonedProxyQuery->addSelect('currency.code');

        $this->assertStringNotContainsString('currency__code', $proxyQuery->getQueryBuilder()->getDQL());
        $this->assertStringContainsString('currency__code', $clonedProxyQuery->getQueryBuilder()->getDQL());
    }

    public function testGeneratedQueryIsValidOnThePostgreSqlPlatform(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $proxyQuery->addSelect('currency.format.pattern');

        $this->assertStringContainsString('SELECT', $proxyQuery->getQueryBuilder()->getQuery()->getSQL());
    }

    public function testResolvedPathDescribesTheTerminalField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $pathResolution = $proxyQuery->resolvePath('currency.code');

        $this->assertSame(PathCardinalityEnum::TO_ONE, $pathResolution->description->cardinality);
        $this->assertFalse($pathResolution->isToMany());
        $this->assertSame('currency_join.code', $pathResolution->dqlExpression);
        $this->assertSame('currency__code', $pathResolution->selectAlias);
        $this->assertSame('string', $pathResolution->fieldType);
    }

    public function testResolvedToManyPathIsNotJoinedIntoTheMainQuery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $pathResolution = $proxyQuery->resolvePath('domains.domainId');

        $this->assertSame(PathCardinalityEnum::TO_MANY, $pathResolution->description->cardinality);
        $this->assertTrue($pathResolution->isToMany());
        $this->assertNull($pathResolution->dqlExpression);
        $this->assertSame([], $proxyQuery->getQueryBuilder()->getDQLPart('join'));
    }

    /**
     * @return array<string, array{string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum}>
     */
    public static function pathDescriptionDataProvider(): array
    {
        return [
            'own field' => ['name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'date field' => ['createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME],
            'field through a to-one association' => ['currency.code', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'identifier of a to-one association' => ['currency.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER],
            'to-one association itself' => ['currency', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION],
            'field through a to-many association' => ['domains.domainId', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::INTEGER],
            'to-many association itself' => ['tags', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION],
            'translated field through a to-many association' => ['labels.name', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::STRING],
        ];
    }

    #[DataProvider('pathDescriptionDataProvider')]
    public function testPathIsDescribedByItsCardinalityAndValueType(
        string $path,
        PathCardinalityEnum $expectedCardinality,
        PathValueTypeEnum $expectedValueType,
    ): void {
        $pathDescription = $this->createProxyQuery(DummyProduct::class)->describePath($path);

        $this->assertSame($path, $pathDescription->path);
        $this->assertSame($expectedCardinality, $pathDescription->cardinality);
        $this->assertSame($expectedValueType, $pathDescription->valueType);
    }

    /**
     * Describing is what a declaration does before any query is built, so it must not prepare the query
     * for a path nobody asked to select or filter.
     */
    public function testDescribingPathLeavesTheQueryUntouched(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->describePath('name');
        $proxyQuery->describePath('translations.description');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testDescribingUnknownPathIsRefused(): void
    {
        $this->expectException(PathNotFoundException::class);

        $this->createProxyQuery(DummyProduct::class)->describePath('currency.unknown');
    }

    /**
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        return new ProxyQuery($entityClass, $this->createEntityManager(), self::LOCALE);
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
