<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalkerInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\WalkedPath;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ToManySelectNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Doctrine\NormalizedFunction;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrencyFormat;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProductDetail;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyWarehouse;

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
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $dql);
        $this->assertStringContainsString('currency_join.code AS currency__code', $dql);
    }

    /**
     * Doctrine selects an entity only through a range variable, which is why the association is joined as one.
     */
    public function testAssociationEntityItselfCanBeSelected(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('id');
        $proxyQuery->addSelect('currency');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('currency_join AS currency', $queryBuilder->getDQL());
        $this->assertStringContainsString('LEFT JOIN DummyCurrency', $queryBuilder->getQuery()->getSQL());
    }

    public function testPathIsResolvedThroughMultipleJoins(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.format.pattern');

        $dql = $proxyQuery->getQueryBuilder()->getDQL();
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $dql);
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join', $dql);
        $this->assertStringContainsString('currency__format_join.pattern AS currency__format__pattern', $dql);
    }

    public function testTwoPathsToTheSameAssociationDoNotShareJoinAlias(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.format.pattern');
        $proxyQuery->addSelect('secondaryCurrency.format.pattern');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('currency__format_join WITH currency_join.format = currency__format_join', $dql);
        $this->assertStringContainsString('secondaryCurrency__format_join WITH secondaryCurrency_join.format = secondaryCurrency__format_join', $dql);
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
        $this->assertSame(1, substr_count($dql, 'currency_join WITH o.currency = currency_join'));
        $this->assertSame(2, substr_count($dql, 'LEFT JOIN'));
    }

    public function testAssociationIsJoinedRegardlessOfHowItsIdentifierIsNamed(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('warehouse.name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * DQL compares an association only on the side owning it, so the inverse side of a one-to-one is joined by
     * the association of the entity it leads to.
     */
    public function testInverseSideOfOneToOneIsJoinedByTheOwningSide(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->addSelect('detail.note');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyProductDetail::class . ' detail_join WITH detail_join.product = o', $queryBuilder->getDQL());
        $this->assertStringContainsString('detail_join.note AS detail__note', $queryBuilder->getDQL());
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * The inverse side holds no column of the identifier, so it is read from the joined entity.
     */
    public function testIdentifierOfInverseSideOfOneToOneIsSelectedFromTheJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->addSelect('detail.id');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('detail_join.id AS detail__id', $queryBuilder->getDQL());
        $this->assertStringNotContainsString('IDENTITY', $queryBuilder->getDQL());
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
     * A related row of a collection is reached by a correlated subquery joining the collection inwardly, under
     * aliases of its own, so that the listed record is never multiplied.
     */
    public function testRelatedRowIsMatchedByCorrelatedSubquery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $predicate = $proxyQuery->exists('domains.domainId', static fn (PathResolution $pathResolution): string => sprintf(
            '%s = :%s',
            $pathResolution->dqlExpression,
            $proxyQuery->addParameter(1),
        ));
        $proxyQuery->applyExpression($predicate);

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(
            'EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0 INNER JOIN dgsub_0.domains dgsub_0_domains_join WHERE dgsub_0.id = o.id AND dgsub_0_domains_join.domainId = :dgexpr_0)',
            $predicate,
        );
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * A project reads a path differently (an own Doctrine type, ...) by an own walker replacing the service.
     */
    public function testPathIsResolvedByTheGivenWalker(): void
    {
        $entityManager = $this->createEntityManager();
        $pathWalker = new class(new PathWalker($entityManager)) implements PathWalkerInterface {
            public function __construct(
                private readonly PathWalkerInterface $innerPathWalker,
            ) {
            }

            public function walk(string $entityClass, string $path): WalkedPath
            {
                $walkedPath = $this->innerPathWalker->walk($entityClass, $path);
                $description = $walkedPath->description;

                return new WalkedPath($walkedPath->steps, new PathDescription($description->path, $description->cardinality, PathValueTypeEnum::MONEY));
            }
        };
        $proxyQuery = new ProxyQuery(DummyProduct::class, $entityManager, $pathWalker, self::LOCALE);

        $this->assertSame(PathValueTypeEnum::MONEY, $proxyQuery->resolvePath('name')->description->valueType);
    }

    /**
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        $entityManager = $this->createEntityManager();

        return new ProxyQuery($entityClass, $entityManager, new PathWalker($entityManager), self::LOCALE);
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
