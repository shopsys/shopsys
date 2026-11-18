<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\Types\Types;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalkerInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\WalkedPath;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\IntegerRange;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrencyFormat;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProductDetail;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyWarehouse;

final class ProxyQueryTest extends TestCase
{
    use QueryAssertionTrait;

    private const string LOCALE = 'cs';

    public function testRootFieldIsSelectedOnTheRootAlias(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('name');

        $this->assertQuery('SELECT o.name AS name FROM ' . DummyProduct::class . ' o', $proxyQuery->getQueryBuilder());
    }

    /**
     * Doctrine selects an entity only through the alias of a join.
     */
    public function testAssociationEntityItselfCanBeSelected(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('id');
        $proxyQuery->addSelect('currency');

        $this->assertQuery(
            'SELECT o.id AS id, currency_join AS currency FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testTwoPathsToTheSameAssociationDoNotShareJoinAlias(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.format.pattern');
        $proxyQuery->addSelect('secondaryCurrency.format.pattern');

        $this->assertQuery(
            'SELECT currency__format_join.pattern AS currency__format__pattern, secondaryCurrency__format_join.pattern AS secondaryCurrency__format__pattern'
            . ' FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join'
            . ' LEFT JOIN ' . DummyCurrency::class . ' secondaryCurrency_join WITH o.secondaryCurrency = secondaryCurrency_join'
            . ' LEFT JOIN ' . DummyCurrencyFormat::class . ' secondaryCurrency__format_join WITH secondaryCurrency_join.format = secondaryCurrency__format_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testTheSameSelectIsAddedOnlyOnce(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.code');
        $proxyQuery->addSelect('currency.code');

        $this->assertQuery(
            'SELECT currency_join.code AS currency__code FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testJoinIsSharedByTwoPathsLeadingThroughIt(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.code');
        $proxyQuery->addSelect('currency.format.pattern');

        $this->assertQuery(
            'SELECT currency_join.code AS currency__code, currency__format_join.pattern AS currency__format__pattern'
            . ' FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testAssociationIsJoinedRegardlessOfHowItsIdentifierIsNamed(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('warehouse.name');

        $this->assertQuery(
            'SELECT warehouse_join.name AS warehouse__name FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * DQL compares an association only on its owning side, so the inverse side of a one-to-one is joined by the
     * association of the joined entity.
     */
    public function testInverseSideOfOneToOneIsJoinedByTheOwningSide(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->addSelect('detail.note');

        $this->assertQuery(
            'SELECT o.id AS id, detail_join.note AS detail__note FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyProductDetail::class . ' detail_join WITH detail_join.product = o',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * The inverse side has no column with the identifier, so the identifier is read from the join.
     */
    public function testIdentifierOfInverseSideOfOneToOneIsSelectedFromTheJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->addSelect('detail.id');

        $this->assertQuery(
            'SELECT o.id AS id, detail_join.id AS detail__id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyProductDetail::class . ' detail_join WITH detail_join.product = o',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testIdentifierOfAssociationIsSelectedWithoutJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('currency.id');

        $this->assertQuery('SELECT IDENTITY(o.currency) AS currency__id FROM ' . DummyProduct::class . ' o', $proxyQuery->getQueryBuilder());
    }

    /**
     * A field of an embeddable is mapped on the entity holding it, so it is addressed through the entity.
     */
    public function testFieldOfEmbeddableIsSelectedThroughTheEntityHoldingIt(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->addSelect('warehouse.address.country');

        $this->assertQuery(
            'SELECT warehouse_join.address.country AS warehouse__address__country FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testTranslatedFieldIsJoinedWithCurrentLocale(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->addSelect('name');

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery(
            'SELECT o_tr.name AS name FROM ' . DummyArticle::class . ' o'
            . ' LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale',
            $queryBuilder,
        );
        $this->assertSame(self::LOCALE, $queryBuilder->getParameter('o_tr_locale')->getValue());
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
        $proxyQuery->addSelect('description');
        $proxyQuery->addSelect('translations.description');

        $this->assertQuery(
            'SELECT o_tr.name AS name, o_tr.description AS description, o_tr.description AS translations__description FROM ' . DummyArticle::class . ' o'
            . ' LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testSelectOfToManyPathIsNotSupported(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(UnsupportedConditionException::class);
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

    /**
     * The clone joins `currency` first, so the original has to join it on its own once it needs it.
     */
    public function testCloneAndOriginalAddTheirJoinsIndependently(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('name');

        $clonedProxyQuery = clone $proxyQuery;
        $clonedProxyQuery->addSelect('currency.code');
        $proxyQuery->addSelect('currency.format.pattern');

        $this->assertQuery(
            'SELECT o.name AS name, currency__format_join.pattern AS currency__format__pattern FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join',
            $proxyQuery->getQueryBuilder(),
        );
        $this->assertQuery(
            'SELECT o.name AS name, currency_join.code AS currency__code FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join',
            $clonedProxyQuery->getQueryBuilder(),
        );
    }

    public function testResolvedPathDescribesTheTerminalField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $pathResolution = $proxyQuery->resolvePath('currency.code');

        $this->assertSame(PathCardinalityEnum::TO_ONE, $pathResolution->description->cardinality);
        $this->assertSame('currency_join.code', $pathResolution->dqlExpression);
        $this->assertSame('currency__code', $pathResolution->selectAlias);
        $this->assertSame('string', $pathResolution->fieldType);
    }

    public function testResolvedToManyPathIsNotJoinedIntoTheMainQuery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $pathResolution = $proxyQuery->resolvePath('domains.domainId');

        $this->assertSame(PathCardinalityEnum::TO_MANY, $pathResolution->description->cardinality);
        $this->assertNull($pathResolution->dqlExpression);
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o', $proxyQuery->getQueryBuilder());
    }

    /**
     * To prevent multiplying rows, a record of a to-many association is reached by an EXISTS subquery with its own
     * aliases.
     */
    public function testToManyPathIsMatchedByExistsSubquery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $predicate = $proxyQuery->exists('domains.domainId', static fn (PathResolution $pathResolution): string => sprintf(
            '%s = :%s',
            $pathResolution->dqlExpression,
            $proxyQuery->addParameter(1),
        ));
        $proxyQuery->applyExpression($predicate);

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.domains dgsub_0_domains_join'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_domains_join.domainId = :dgexpr_0)',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testConditionOfCloneNeverReachesTheOriginal(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $clonedProxyQuery = clone $proxyQuery;
        $currencyCode = $clonedProxyQuery->resolvePath('currency.code');
        $clonedProxyQuery->applyExpression(sprintf('%s = :%s', $currencyCode->dqlExpression, $clonedProxyQuery->addParameter('CZK')));
        $clonedProxyQuery->applyExpression($clonedProxyQuery->exists('labels.name', static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NOT NULL'));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' WHERE currency_join.code = :dgexpr_0'
            . ' AND (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.labels dgsub_0_labels_join'
            . ' LEFT JOIN dgsub_0_labels_join.translations dgsub_0_labels_join_tr WITH dgsub_0_labels_join_tr.locale = :dgsub_0_labels_join_tr_locale'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_labels_join_tr.name IS NOT NULL))',
            $clonedProxyQuery->getQueryBuilder(),
        );
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o', $proxyQuery->getQueryBuilder());
        $this->assertCount(0, $proxyQuery->getQueryBuilder()->getParameters());
    }

    /**
     * A project reads a path differently (a Doctrine type of its own, …) by its own walker decorating `PathWalker`.
     */
    public function testPathIsResolvedByTheGivenWalker(): void
    {
        $entityManager = DummyEntityManagerFactory::create();
        $pathWalker = new readonly class(new PathWalker($entityManager)) implements PathWalkerInterface {
            public function __construct(
                private PathWalker $pathWalker,
            ) {
            }

            #[Override]
            public function walk(string $entityClass, string $path): WalkedPath
            {
                $walkedPath = $this->pathWalker->walk($entityClass, $path);

                if ($walkedPath->getFieldType() !== Types::SMALLINT) {
                    return $walkedPath;
                }

                return new WalkedPath($walkedPath->steps, $walkedPath->description->withValueType(PathValueTypeEnum::DECIMAL));
            }
        };
        $proxyQuery = new ProxyQuery(DummyProduct::class, $entityManager, $pathWalker, self::LOCALE);

        $this->assertEquals(
            new PathDescription('rating', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DECIMAL, null, new IntegerRange(-32768, 32767)),
            $proxyQuery->resolvePath('rating')->description,
        );
        $this->assertSame(PathValueTypeEnum::STRING, $proxyQuery->resolvePath('name')->description->valueType);
    }

    /**
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        $entityManager = DummyEntityManagerFactory::create();

        return new ProxyQuery($entityClass, $entityManager, new PathWalker($entityManager), self::LOCALE);
    }
}
