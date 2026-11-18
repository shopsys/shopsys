<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Expression;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrencyFormat;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyWarehouse;

final class DqlExpressionBuilderTest extends TestCase
{
    private const string LOCALE = 'cs';

    public function testExpressionOnRootFieldIsAppliedWithParameter(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::EQUALS, 'Foo')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('o.name = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('Foo', $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    public function testExpressionOnAssociationPathJoinsTheAssociation(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('currency.code', ExpressionOperatorEnum::CONTAINS, 'CZ')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('NORMALIZED(currency_join.code) LIKE NORMALIZED(:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionOnDeepAssociationPathJoinsEveryAssociationOnTheWay(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('currency.format.pattern', ExpressionOperatorEnum::EQUALS, '#')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $dql);
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join', $dql);
        $this->assertStringContainsString('currency__format_join.pattern = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * The association is joined outwardly, so that a record without it never disappears from the listing
     * only because an expression mentions it.
     */
    public function testAssociationOfAnExpressionIsJoinedOutwardly(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('warehouse.name', ExpressionOperatorEnum::IS_NULL, null)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('warehouse_join.name IS NULL', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('LEFT JOIN', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionSharesTheJoinWithSelectOfTheSamePath(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('currency.code');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('currency.code', ExpressionOperatorEnum::EQUALS, 'CZK')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(1, substr_count($queryBuilder->getDQL(), 'LEFT JOIN'));
        $this->assertStringContainsString('currency_join.code = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
    }

    public function testTwoExpressionsOnTheSamePathShareTheJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('currency.code', ExpressionOperatorEnum::EQUALS, 'CZK')));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('currency.code', ExpressionOperatorEnum::NOT_EQUALS, 'EUR')));

        $this->assertSame(1, substr_count($proxyQuery->getQueryBuilder()->getDQL(), 'LEFT JOIN'));
    }

    public function testExpressionOnTranslatedFieldUsesTheLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::EQUALS, 'Foo')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(1, substr_count($queryBuilder->getDQL(), 'LEFT JOIN o.translations o_tr'));
        $this->assertStringContainsString('o_tr.name = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
    }

    public function testExpressionOnExplicitlyAddressedTranslatedFieldUsesTheLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyShadowedArticle::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('translations.name', ExpressionOperatorEnum::EQUALS, 'Foo')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('o_tr.name = :dgexpr_0', $where);
        $this->assertStringNotContainsString('EXISTS', $where);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testEachValueGetsItsOwnParameter(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::EQUALS, 1)));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::BETWEEN, [5, 10])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('o.id = :dgexpr_0', $where);
        $this->assertStringContainsString('o.id BETWEEN :dgexpr_1 AND :dgexpr_2', $where);
        $this->assertCount(3, $queryBuilder->getParameters());
    }

    /**
     * Without the type of the field the value would be bound as a string and a date would never match.
     */
    public function testValueIsBoundWithTheTypeOfTheComparedField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $createdAt = new DateTimeImmutable('2026-01-01');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('createdAt', ExpressionOperatorEnum::GREATER_THAN, $createdAt)));

        $parameter = $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0');
        $this->assertEquals($createdAt, $parameter->getValue());
        $this->assertSame('datetime_immutable', $parameter->getType());
        $this->assertStringContainsString('SELECT', $proxyQuery->getQueryBuilder()->getQuery()->getSQL());
    }

    /**
     * Doctrine converts a date only of the class its type declares, so a date of the other class is converted
     * first — a filter makes an immutable date whatever the column is.
     */
    public function testDateIsBoundAsTheClassTheTypeOfTheFieldConverts(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::greaterThan('updatedAt', new DateTimeImmutable('2026-01-01 10:00:00'))));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::greaterThan('createdAt', new DateTime('2026-01-01 10:00:00'))));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertInstanceOf(DateTime::class, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertEquals(new DateTime('2026-01-01 10:00:00'), $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertInstanceOf(DateTimeImmutable::class, $queryBuilder->getParameter('dgexpr_1')->getValue());
        $this->assertEquals(new DateTimeImmutable('2026-01-01 10:00:00'), $queryBuilder->getParameter('dgexpr_1')->getValue());
    }

    /**
     * A date and time is written without its time zone and read back in the default one, so a moment given in
     * another time zone is converted to the default one first — it means the same moment it does in memory.
     */
    public function testDateAndTimeIsBoundAsTheSameMomentInTheDefaultTimeZone(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $moment = new DateTimeImmutable('2026-01-01 01:00:00', new DateTimeZone('Asia/Tokyo'));

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::greaterThan('createdAt', $moment)));

        /** @var \DateTimeImmutable $boundMoment */
        $boundMoment = $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0')->getValue();
        $this->assertSame(date_default_timezone_get(), $boundMoment->getTimezone()->getName());
        $this->assertSame($moment->getTimestamp(), $boundMoment->getTimestamp());
    }

    /**
     * A date without time is a day of the calendar, so it is never moved to another time zone — that could change
     * the day.
     */
    public function testDateWithoutTimeIsBoundAsTheSameDay(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::equals('releasedOn', new DateTimeImmutable('2026-05-15 00:00:00', new DateTimeZone('Pacific/Kiritimati')))));

        /** @var \DateTime $boundDay */
        $boundDay = $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0')->getValue();
        $this->assertInstanceOf(DateTime::class, $boundDay);
        $this->assertSame('2026-05-15', $boundDay->format('Y-m-d'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function likeValueDataProvider(): array
    {
        return [
            'wildcard for any sequence' => ['100%', '%100\\%%'],
            'wildcard for a single character' => ['a_b', '%a\\_b%'],
            'the escape character itself' => ['a\\b', '%a\\\\b%'],
        ];
    }

    /**
     * A value the administrator typed is always taken literally, so the wildcards of LIKE are escaped.
     */
    #[DataProvider('likeValueDataProvider')]
    public function testWildcardsOfTheSearchedValueAreEscaped(string $value, string $expectedPattern): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, $value)));

        $this->assertSame($expectedPattern, $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0')->getValue());
    }

    /**
     * Both sides are normalized by the database, so the value is bound as typed and the case and the
     * diacritics are settled under one definition.
     */
    public function testSearchedValueIsNormalizedByTheDatabase(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::STARTS_WITH, 'FOO')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('FOO%', $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    public function testEmptyListOfValuesMatchesNothing(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('id', ExpressionOperatorEnum::IN, [])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('1 = 0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testEmptyListOfExcludedValuesMatchesEverything(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('id', ExpressionOperatorEnum::NOT_IN, [])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('1 = 1', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testExpressionOnOneToManyPathIsMatchedBySubquery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0', $where);
        $this->assertStringContainsString('INNER JOIN dgsub_0.domains dgsub_0_domains_join', $where);
        $this->assertStringContainsString('dgsub_0.id = o.id', $where);
        $this->assertStringContainsString('dgsub_0_domains_join.domainId IN (:dgexpr_0)', $where);
        // the listed entity must never be multiplied by its related rows
        $this->assertSame([], $queryBuilder->getDQLPart('join'));
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionOnManyToManyPathIsMatchedBySubquery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('tags.name', ExpressionOperatorEnum::EQUALS, 'sale')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('INNER JOIN dgsub_0.tags dgsub_0_tags_join', $where);
        $this->assertStringContainsString('dgsub_0_tags_join.name = :dgexpr_0', $where);
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testTwoSubqueriesCoexist(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2])));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('tags.name', ExpressionOperatorEnum::EQUALS, 'sale')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('dgsub_0_domains_join.domainId IN (:dgexpr_0)', $where);
        $this->assertStringContainsString('dgsub_1_tags_join.name = :dgexpr_1', $where);
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * A translated field is reachable through a to-many path as well. The subquery joins the translations
     * with the current locale on its own, and the parameter of that join has to be carried over to the
     * query that is actually executed.
     */
    public function testSubqueryReachesTranslatedFieldWithItsOwnLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('labels.name', ExpressionOperatorEnum::EQUALS, 'Sale')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('INNER JOIN dgsub_0.labels dgsub_0_labels_join', $where);
        $this->assertStringContainsString('LEFT JOIN dgsub_0_labels_join.translations dgsub_0_labels_join_tr', $where);
        $this->assertStringContainsString('dgsub_0_labels_join_tr.name = :dgexpr_0', $where);
        $this->assertSame(self::LOCALE, $queryBuilder->getParameter('dgsub_0_labels_join_tr_locale')->getValue());
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testSubqueryAliasesDoNotShadowTheAliasesOfTheQueryItIsNestedIn(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $proxyQuery->addSelect('currency.code');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::EQUALS, 2)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('dgsub_0_domains_join', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionsAreCombinedByConjunction(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::andX(
            Condition::equals('name', 'Foo'),
            Condition::orX(Condition::equals('id', 1), Condition::not(Condition::isNull('warehouse.name'))),
        )));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('o.name = :dgexpr_0 AND (o.id = :dgexpr_1 OR CASE WHEN (warehouse_join.name IS NULL) THEN 1 ELSE 0 END = 0)', $where);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * Negating a whole expression asks something else than the negating operation of that expression,
     * once the path leads through a to-many association.
     */
    public function testNegatingAnExpressionOverToManyPathDiffersFromTheNegatingOperation(): void
    {
        $negatedQuery = $this->createProxyQuery(DummyProduct::class);
        $negatedQuery->applyExpression($this->createExpressionBuilder($negatedQuery)->compile(Condition::not(Condition::equals('tags.name', 'sale'))));

        $negatingQuery = $this->createProxyQuery(DummyProduct::class);
        $negatingQuery->applyExpression($this->createExpressionBuilder($negatingQuery)->compile(new Comparison('tags.name', ExpressionOperatorEnum::NOT_EQUALS, 'sale')));

        // no related row is named sale
        $this->assertStringStartsWith('CASE WHEN (EXISTS(', (string)$negatedQuery->getQueryBuilder()->getDQLPart('where'));
        // some related row is not named sale
        $negatingWhere = (string)$negatingQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringStartsWith('EXISTS(', $negatingWhere);
        $this->assertStringContainsString('name <> :dgexpr_0', $negatingWhere);
    }

    public function testEmptinessOfCollectionIsMatchedBySubqueryOfPresentValues(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('tags', ExpressionOperatorEnum::IS_EMPTY, null)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringStartsWith('NOT (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0 INNER JOIN dgsub_0.tags dgsub_0_tags_join WHERE dgsub_0.id = o.id AND dgsub_0_tags_join IS NOT NULL))', $where);
        $this->assertCount(0, $queryBuilder->getParameters());
        $this->assertStringContainsString('NOT (EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testNonEmptinessOfCollectionIsMatchedBySubqueryOfPresentValues(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('domains', ExpressionOperatorEnum::IS_NOT_EMPTY, null)));

        $where = (string)$proxyQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringStartsWith('EXISTS(SELECT 1 FROM', $where);
        $this->assertStringContainsString('INNER JOIN dgsub_0.domains dgsub_0_domains_join WHERE dgsub_0.id = o.id AND dgsub_0_domains_join IS NOT NULL)', $where);
    }

    /**
     * The subquery joins a to-one association outwardly, so a related row without the association has a missing
     * value there — a negation matches it the way it matches a listed record without the association.
     */
    public function testToOneAssociationBehindCollectionIsJoinedOutwardly(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::notEquals('domains.product.name', 'Foo')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('INNER JOIN dgsub_0.domains dgsub_0_domains_join LEFT JOIN dgsub_0_domains_join.product dgsub_0_domains__product_join', $where);
        $this->assertStringContainsString('(dgsub_0_domains__product_join.name <> :dgexpr_0 OR dgsub_0_domains__product_join.name IS NULL)', $where);
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * A related row without the value is no related row to count — an order item without a product is no product
     * of the order.
     */
    public function testEmptinessBehindCollectionCountsOnlyRowsWithTheValue(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::isEmpty('domains.product')));

        $where = (string)$proxyQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringContainsString('LEFT JOIN dgsub_0_domains_join.product dgsub_0_domains__product_join WHERE dgsub_0.id = o.id AND dgsub_0_domains__product_join IS NOT NULL', $where);
    }

    /**
     * A collection of an association that may be missing is joined outwardly as well: an empty collection has
     * no related row, while a missing owner is a missing value, the same way a listed record without it has one.
     */
    public function testCollectionOfMissingOwnerIsMissingValueWhileEmptyCollectionHasNoRow(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::notEquals('detail.product.tags.name', 'sale')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('LEFT JOIN dgsub_0.detail dgsub_0_detail_join LEFT JOIN dgsub_0_detail_join.product dgsub_0_detail__product_join LEFT JOIN dgsub_0_detail__product_join.tags dgsub_0_detail__product__tags_join', $where);
        $this->assertStringContainsString('WHERE dgsub_0.id = o.id AND (dgsub_0_detail__product__tags_join IS NOT NULL OR dgsub_0_detail__product_join IS NULL) AND', $where);
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * A text search on a date fails in the database, so it is refused with a reason instead.
     */
    public function testTextOperationOnNonTextPathIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('Operator "contains" cannot be applied to path "createdAt"');

        $this->createExpressionBuilder($proxyQuery)->compile(new Comparison('createdAt', ExpressionOperatorEnum::CONTAINS, '2026'));
    }

    /**
     * The subquery joins the related rows inwardly, so a null test on a collection could never match.
     */
    public function testNullTestOnCollectionIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('use isEmpty or isNotEmpty');

        $this->createExpressionBuilder($proxyQuery)->compile(new Comparison('tags.name', ExpressionOperatorEnum::IS_NULL, null));
    }

    public function testEmptinessTestOnSingleValueIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('only a collection can be empty');

        $this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::IS_EMPTY, null));
    }

    public function testNoExpressionAtAllMatchesEverything(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->assertSame('1 = 1', (string)$this->createExpressionBuilder($proxyQuery)->compile(Condition::andX()));
        $this->assertSame('1 = 0', (string)$this->createExpressionBuilder($proxyQuery)->compile(Condition::orX()));
    }

    /**
     * SQL leaves out a record whose value is missing even when the comparison is negated, so every negation tests
     * the missing value explicitly — a record without a value is "not X", as it is in memory.
     */
    public function testNegationMatchesRecordWithoutValue(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::notEquals('warehouse.name', 'Main')));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::notIn('currency.id', [1, 2])));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::not(Condition::contains('name', 'Foo'))));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('(warehouse_join.name <> :dgexpr_0 OR warehouse_join.name IS NULL)', $where);
        $this->assertStringContainsString('(IDENTITY(o.currency) NOT IN (:dgexpr_1, :dgexpr_2) OR IDENTITY(o.currency) IS NULL)', $where);
        $this->assertStringContainsString('CASE WHEN (NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_3)) THEN 1 ELSE 0 END = 0', $where);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * A list parameter of Doctrine binds only integers and strings, so every value of a list is bound on its own
     * with the type of the compared field — a boolean or a date would never match otherwise.
     */
    public function testEveryValueOfListIsBoundWithTheTypeOfTheComparedField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $createdAt = new DateTimeImmutable('2026-01-01');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::in('createdAt', [$createdAt])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('o.createdAt IN (:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertEquals($createdAt, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertSame('datetime_immutable', $queryBuilder->getParameter('dgexpr_0')->getType());
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * @return array<string, array{string, string, mixed, string}>
     */
    public static function operatorDataProvider(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS => [ExpressionOperatorEnum::EQUALS, 'name', 'Foo', 'o.name = :dgexpr_0'],
            ExpressionOperatorEnum::NOT_EQUALS => [ExpressionOperatorEnum::NOT_EQUALS, 'name', 'Foo', '(o.name <> :dgexpr_0 OR o.name IS NULL)'],
            ExpressionOperatorEnum::CONTAINS => [ExpressionOperatorEnum::CONTAINS, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)'],
            ExpressionOperatorEnum::STARTS_WITH => [ExpressionOperatorEnum::STARTS_WITH, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)'],
            ExpressionOperatorEnum::ENDS_WITH => [ExpressionOperatorEnum::ENDS_WITH, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)'],
            ExpressionOperatorEnum::GREATER_THAN => [ExpressionOperatorEnum::GREATER_THAN, 'id', 1, 'o.id > :dgexpr_0'],
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 'id', 1, 'o.id >= :dgexpr_0'],
            ExpressionOperatorEnum::LESS_THAN => [ExpressionOperatorEnum::LESS_THAN, 'id', 1, 'o.id < :dgexpr_0'],
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => [ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 'id', 1, 'o.id <= :dgexpr_0'],
            ExpressionOperatorEnum::BETWEEN => [ExpressionOperatorEnum::BETWEEN, 'id', [1, 5], 'o.id BETWEEN :dgexpr_0 AND :dgexpr_1'],
            ExpressionOperatorEnum::IN => [ExpressionOperatorEnum::IN, 'id', [1, 5], 'o.id IN (:dgexpr_0, :dgexpr_1)'],
            ExpressionOperatorEnum::NOT_IN => [ExpressionOperatorEnum::NOT_IN, 'id', [1, 5], '(o.id NOT IN (:dgexpr_0, :dgexpr_1) OR o.id IS NULL)'],
            ExpressionOperatorEnum::IS_NULL => [ExpressionOperatorEnum::IS_NULL, 'name', null, 'o.name IS NULL'],
            ExpressionOperatorEnum::IS_NOT_NULL => [ExpressionOperatorEnum::IS_NOT_NULL, 'name', null, 'o.name IS NOT NULL'],
            ExpressionOperatorEnum::IS_EMPTY => [ExpressionOperatorEnum::IS_EMPTY, 'tags', null, 'NOT (EXISTS(SELECT 1 FROM'],
            ExpressionOperatorEnum::IS_NOT_EMPTY => [ExpressionOperatorEnum::IS_NOT_EMPTY, 'tags', null, 'EXISTS(SELECT 1 FROM'],
        ];
    }

    #[DataProvider('operatorDataProvider')]
    public function testOperatorIsExpressedInDql(
        string $operator,
        string $path,
        mixed $value,
        string $expectedDql,
    ): void {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison($path, $operator, $value)));

        $this->assertStringContainsString($expectedDql, (string)$proxyQuery->getQueryBuilder()->getDQLPart('where'));
        $this->assertStringContainsString('SELECT', $proxyQuery->getQueryBuilder()->getQuery()->getSQL());
    }

    /**
     * The vocabulary and the builder must not drift apart — every case the enum lists has a branch in `compare()`.
     */
    public function testEveryOperatorOfTheVocabularyIsExpressed(): void
    {
        $this->assertEqualsCanonicalizing((new ExpressionOperatorEnum())->getAllCases(), array_keys(self::operatorDataProvider()));
    }

    /**
     * A project adds an operation to the vocabulary by a subclass of the enum; a medium it did not teach the
     * operation refuses it.
     */
    public function testOperationTheMediumDoesNotExpressIsRefused(): void
    {
        $expressionOperatorEnum = new class() extends ExpressionOperatorEnum {
            public const string JSON_CONTAINS = 'jsonContains';
        };
        $expressionBuilder = new DqlExpressionBuilder($this->createProxyQuery(DummyProduct::class), $expressionOperatorEnum);

        $this->expectException(OperatorNotSupportedException::class);

        $expressionBuilder->compile(new Comparison('name', 'jsonContains', 'Foo'));
    }

    private function createExpressionBuilder(ProxyQuery $proxyQuery): DqlExpressionBuilder
    {
        return new DqlExpressionBuilder($proxyQuery, new ExpressionOperatorEnum());
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
