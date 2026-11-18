<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Expression;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\Query\Parameter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrencyFormat;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProductDetail;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyWarehouse;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\QueryAssertionTrait;

final class DqlExpressionBuilderTest extends TestCase
{
    use QueryAssertionTrait;

    private const string LOCALE = 'cs';

    public function testExpressionOnDeepAssociationPathJoinsEveryAssociationOnTheWay(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('currency.format.pattern', ExpressionOperatorEnum::EQUALS, '#')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' LEFT JOIN ' . DummyCurrencyFormat::class . ' currency__format_join WITH currency_join.format = currency__format_join'
            . ' WHERE currency__format_join.pattern = :dgexpr_0',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * A LEFT JOIN keeps a record without the association in the list.
     */
    public function testAssociationOfAnExpressionIsLeftJoined(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('warehouse.name', ExpressionOperatorEnum::IS_NULL, null)));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join'
            . ' WHERE warehouse_join.name IS NULL',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testTwoExpressionsOnTheSamePathShareTheJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('currency.code', ExpressionOperatorEnum::EQUALS, 'CZK')));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('currency.code', ExpressionOperatorEnum::NOT_EQUALS, 'EUR')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyCurrency::class . ' currency_join WITH o.currency = currency_join'
            . ' WHERE currency_join.code = :dgexpr_0 AND ((currency_join.code <> :dgexpr_1 OR currency_join.code IS NULL))',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testExpressionOnTranslatedFieldUsesTheLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::EQUALS, 'Foo')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyArticle::class . ' o'
            . ' LEFT JOIN o.translations o_tr WITH o_tr.locale = :o_tr_locale'
            . ' WHERE o_tr.name = :dgexpr_0',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testEachValueGetsItsOwnParameter(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::EQUALS, 1)));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('id', ExpressionOperatorEnum::BETWEEN, [5, 10])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE o.id = :dgexpr_0 AND (o.id BETWEEN :dgexpr_1 AND :dgexpr_2)',
            $queryBuilder,
        );
        $this->assertCount(3, $queryBuilder->getParameters());
    }

    /**
     * Without the type of the field, the value would be bound as a string and a date would never match.
     */
    public function testValueIsBoundWithTheTypeOfTheComparedField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $createdAt = new DateTimeImmutable('2026-01-01');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('createdAt', ExpressionOperatorEnum::GREATER_THAN, $createdAt)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.createdAt > :dgexpr_0', $queryBuilder);
        $this->assertEquals($createdAt, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertSame('datetime_immutable', $queryBuilder->getParameter('dgexpr_0')->getType());
    }

    /**
     * Doctrine converts only a date of the class its type declares, so a date of the other class is converted first.
     * A filter makes an immutable date whatever the column is.
     */
    public function testDateIsBoundAsTheClassTheTypeOfTheFieldConverts(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::greaterThan('updatedAt', new DateTimeImmutable('2026-01-01 10:00:00'))));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::greaterThan('createdAt', new DateTime('2026-01-01 10:00:00'))));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE o.updatedAt > :dgexpr_0 AND o.createdAt > :dgexpr_1',
            $queryBuilder,
        );
        $this->assertInstanceOf(DateTime::class, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertEquals(new DateTime('2026-01-01 10:00:00'), $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertInstanceOf(DateTimeImmutable::class, $queryBuilder->getParameter('dgexpr_1')->getValue());
        $this->assertEquals(new DateTimeImmutable('2026-01-01 10:00:00'), $queryBuilder->getParameter('dgexpr_1')->getValue());
    }

    /**
     * A date and time is stored without its time zone and read back in the default one. A moment in another time
     * zone is therefore converted to the default one first, so it stays the same moment.
     */
    public function testDateAndTimeIsBoundAsTheSameMomentInTheDefaultTimeZone(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $moment = new DateTimeImmutable('2026-01-01 01:00:00', new DateTimeZone('Asia/Tokyo'));

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::greaterThan('createdAt', $moment)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.createdAt > :dgexpr_0', $queryBuilder);
        /** @var \DateTimeImmutable $boundMoment */
        $boundMoment = $queryBuilder->getParameter('dgexpr_0')->getValue();
        $this->assertSame(date_default_timezone_get(), $boundMoment->getTimezone()->getName());
        $this->assertSame($moment->getTimestamp(), $boundMoment->getTimestamp());
    }

    /**
     * A date without time is a calendar day. It is never moved to another time zone, as that could change the day.
     */
    public function testDateWithoutTimeIsBoundAsTheSameDay(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::equals('releasedOn', new DateTimeImmutable('2026-05-15 00:00:00', new DateTimeZone('Pacific/Kiritimati')))));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.releasedOn = :dgexpr_0', $queryBuilder);
        /** @var \DateTime $boundDay */
        $boundDay = $queryBuilder->getParameter('dgexpr_0')->getValue();
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

    #[DataProvider('likeValueDataProvider')]
    public function testWildcardsOfTheSearchedValueAreEscaped(string $value, string $expectedPattern): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::CONTAINS, $value)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', $queryBuilder);
        $this->assertSame($expectedPattern, $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    /**
     * The database normalizes both sides, so the value is bound as typed. Case and diacritics are then handled by
     * one definition.
     */
    public function testSearchedValueIsNormalizedByTheDatabase(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('name', ExpressionOperatorEnum::STARTS_WITH, 'FOO')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', $queryBuilder);
        $this->assertSame('FOO%', $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    public function testTwoSubqueriesCoexist(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('domains.domainId', ExpressionOperatorEnum::IN, [2])));
        $proxyQuery->applyExpression($expressionBuilder->compile(new Comparison('tags.name', ExpressionOperatorEnum::EQUALS, 'sale')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.domains dgsub_0_domains_join'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_domains_join.domainId IN (:dgexpr_0)))'
            . ' AND (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_1'
            . ' INNER JOIN dgsub_1.tags dgsub_1_tags_join'
            . ' WHERE dgsub_1.id = o.id AND dgsub_1_tags_join.name = :dgexpr_1))',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * The locale parameter of the join inside the subquery is copied to the query that is executed.
     */
    public function testSubqueryReachesTranslatedFieldWithItsOwnLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison('labels.name', ExpressionOperatorEnum::EQUALS, 'Sale')));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.labels dgsub_0_labels_join'
            . ' LEFT JOIN dgsub_0_labels_join.translations dgsub_0_labels_join_tr WITH dgsub_0_labels_join_tr.locale = :dgsub_0_labels_join_tr_locale'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_labels_join_tr.name = :dgexpr_0)',
            $queryBuilder,
        );
        $this->assertSame(self::LOCALE, $queryBuilder->getParameter('dgsub_0_labels_join_tr_locale')->getValue());
    }

    public function testSubqueryAliasesDoNotShadowTheAliasesOfTheQueryItIsNestedIn(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('detail.note');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::equals('detail.product.tags.name', 'sale')));

        $this->assertQuery(
            'SELECT o.id AS id, detail_join.note AS detail__note FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyProductDetail::class . ' detail_join WITH detail_join.product = o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' LEFT JOIN dgsub_0.detail dgsub_0_detail_join'
            . ' LEFT JOIN dgsub_0_detail_join.product dgsub_0_detail__product_join'
            . ' LEFT JOIN dgsub_0_detail__product_join.tags dgsub_0_detail__product__tags_join'
            . ' WHERE dgsub_0.id = o.id'
            . ' AND (dgsub_0_detail__product__tags_join IS NOT NULL OR dgsub_0_detail__product_join IS NULL)'
            . ' AND dgsub_0_detail__product__tags_join.name = :dgexpr_0)',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testExpressionsAreCombinedByConjunction(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::andX(
            Condition::equals('name', 'Foo'),
            Condition::orX(Condition::equals('id', 1), Condition::not(Condition::isNull('warehouse.name'))),
        )));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join'
            . ' WHERE o.name = :dgexpr_0 AND (o.id = :dgexpr_1 OR CASE WHEN (warehouse_join.name IS NULL) THEN 1 ELSE 0 END = 0)',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testNegatingAnExpressionOverToManyPathDiffersFromTheNegatingOperation(): void
    {
        $negatedQuery = $this->createProxyQuery(DummyProduct::class);
        $negatedQuery->applyExpression($this->createExpressionBuilder($negatedQuery)->compile(Condition::not(Condition::equals('tags.name', 'sale'))));

        $negatingQuery = $this->createProxyQuery(DummyProduct::class);
        $negatingQuery->applyExpression($this->createExpressionBuilder($negatingQuery)->compile(new Comparison('tags.name', ExpressionOperatorEnum::NOT_EQUALS, 'sale')));

        // no related row is named sale
        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE CASE WHEN (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.tags dgsub_0_tags_join'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_tags_join.name = :dgexpr_0)) THEN 1 ELSE 0 END = 0',
            $negatedQuery->getQueryBuilder(),
        );
        // some related row is not named sale
        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.tags dgsub_0_tags_join'
            . ' WHERE dgsub_0.id = o.id AND ((dgsub_0_tags_join.name <> :dgexpr_0 OR dgsub_0_tags_join.name IS NULL)))',
            $negatingQuery->getQueryBuilder(),
        );
    }

    /**
     * A related row without the association has a missing value, which a negation matches, as on a listed record.
     */
    public function testToOneAssociationBehindCollectionIsLeftJoined(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::notEquals('domains.product.name', 'Foo')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.domains dgsub_0_domains_join'
            . ' LEFT JOIN dgsub_0_domains_join.product dgsub_0_domains__product_join'
            . ' WHERE dgsub_0.id = o.id AND ((dgsub_0_domains__product_join.name <> :dgexpr_0 OR dgsub_0_domains__product_join.name IS NULL)))',
            $proxyQuery->getQueryBuilder(),
        );
    }

    public function testEmptinessBehindCollectionCountsOnlyRowsWithTheValue(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::isEmpty('domains.product')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE NOT (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' INNER JOIN dgsub_0.domains dgsub_0_domains_join'
            . ' LEFT JOIN dgsub_0_domains_join.product dgsub_0_domains__product_join'
            . ' WHERE dgsub_0.id = o.id AND dgsub_0_domains__product_join IS NOT NULL))',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * A collection behind an optional association is joined by LEFT JOIN as well.
     */
    public function testCollectionOfMissingOwnerIsMissingValueWhileEmptyCollectionHasNoRow(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::notEquals('detail.product.tags.name', 'sale')));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' WHERE EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0'
            . ' LEFT JOIN dgsub_0.detail dgsub_0_detail_join'
            . ' LEFT JOIN dgsub_0_detail_join.product dgsub_0_detail__product_join'
            . ' LEFT JOIN dgsub_0_detail__product_join.tags dgsub_0_detail__product__tags_join'
            . ' WHERE dgsub_0.id = o.id'
            . ' AND (dgsub_0_detail__product__tags_join IS NOT NULL OR dgsub_0_detail__product_join IS NULL)'
            . ' AND ((dgsub_0_detail__product__tags_join.name <> :dgexpr_0 OR dgsub_0_detail__product__tags_join.name IS NULL)))',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * A text search on a date fails in the database, so it is refused with a reason instead.
     */
    public function testTextOperationOnNonTextPathIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Operator "contains" cannot be applied to path "createdAt"');

        $this->createExpressionBuilder($proxyQuery)->compile(new Comparison('createdAt', ExpressionOperatorEnum::CONTAINS, '2026'));
    }

    public function testNoExpressionAtAllMatchesEverything(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->assertSame('1 = 1', (string)$this->createExpressionBuilder($proxyQuery)->compile(Condition::andX()));
        $this->assertSame('1 = 0', (string)$this->createExpressionBuilder($proxyQuery)->compile(Condition::orX()));
    }

    /**
     * SQL leaves out a record with a missing value even when the comparison is negated. Every negation therefore
     * tests the missing value explicitly, so a record without a value is "not X".
     */
    public function testNegationMatchesRecordWithoutValue(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::notEquals('warehouse.name', 'Main')));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::notIn('currency.id', [1, 2])));
        $proxyQuery->applyExpression($expressionBuilder->compile(Condition::not(Condition::contains('name', 'Foo'))));

        $this->assertQuery(
            'SELECT o.id AS id FROM ' . DummyProduct::class . ' o'
            . ' LEFT JOIN ' . DummyWarehouse::class . ' warehouse_join WITH o.warehouse = warehouse_join'
            . ' WHERE ((warehouse_join.name <> :dgexpr_0 OR warehouse_join.name IS NULL))'
            . ' AND ((IDENTITY(o.currency) NOT IN (:dgexpr_1, :dgexpr_2) OR IDENTITY(o.currency) IS NULL))'
            . ' AND CASE WHEN (NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_3)) THEN 1 ELSE 0 END = 0',
            $proxyQuery->getQueryBuilder(),
        );
    }

    /**
     * A list parameter of Doctrine binds only integers and strings. Every value of a list is therefore bound on its
     * own with the type of the compared field, otherwise a boolean or a date would never match.
     */
    public function testEveryValueOfListIsBoundWithTheTypeOfTheComparedField(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $createdAt = new DateTimeImmutable('2026-01-01');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(Condition::in('createdAt', [$createdAt])));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE o.createdAt IN (:dgexpr_0)', $queryBuilder);
        $this->assertEquals($createdAt, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertSame('datetime_immutable', $queryBuilder->getParameter('dgexpr_0')->getType());
    }

    /**
     * @return array<string, array{string, string, mixed, string, list<mixed>}>
     */
    public static function operatorDataProvider(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS => [ExpressionOperatorEnum::EQUALS, 'name', 'Foo', 'o.name = :dgexpr_0', ['Foo']],
            ExpressionOperatorEnum::NOT_EQUALS => [ExpressionOperatorEnum::NOT_EQUALS, 'name', 'Foo', '(o.name <> :dgexpr_0 OR o.name IS NULL)', ['Foo']],
            ExpressionOperatorEnum::CONTAINS => [ExpressionOperatorEnum::CONTAINS, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', ['%Foo%']],
            ExpressionOperatorEnum::STARTS_WITH => [ExpressionOperatorEnum::STARTS_WITH, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', ['Foo%']],
            ExpressionOperatorEnum::ENDS_WITH => [ExpressionOperatorEnum::ENDS_WITH, 'name', 'Foo', 'NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', ['%Foo']],
            ExpressionOperatorEnum::GREATER_THAN => [ExpressionOperatorEnum::GREATER_THAN, 'id', 1, 'o.id > :dgexpr_0', [1]],
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, 'id', 1, 'o.id >= :dgexpr_0', [1]],
            ExpressionOperatorEnum::LESS_THAN => [ExpressionOperatorEnum::LESS_THAN, 'id', 1, 'o.id < :dgexpr_0', [1]],
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => [ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, 'id', 1, 'o.id <= :dgexpr_0', [1]],
            ExpressionOperatorEnum::BETWEEN => [ExpressionOperatorEnum::BETWEEN, 'id', [1, 5], 'o.id BETWEEN :dgexpr_0 AND :dgexpr_1', [1, 5]],
            ExpressionOperatorEnum::IN => [ExpressionOperatorEnum::IN, 'id', [1, 5], 'o.id IN (:dgexpr_0, :dgexpr_1)', [1, 5]],
            ExpressionOperatorEnum::NOT_IN => [ExpressionOperatorEnum::NOT_IN, 'id', [1, 5], '(o.id NOT IN (:dgexpr_0, :dgexpr_1) OR o.id IS NULL)', [1, 5]],
            ExpressionOperatorEnum::IS_NULL => [ExpressionOperatorEnum::IS_NULL, 'name', null, 'o.name IS NULL', []],
            ExpressionOperatorEnum::IS_NOT_NULL => [ExpressionOperatorEnum::IS_NOT_NULL, 'name', null, 'o.name IS NOT NULL', []],
            ExpressionOperatorEnum::IS_EMPTY => [ExpressionOperatorEnum::IS_EMPTY, 'tags', null, 'NOT (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0 INNER JOIN dgsub_0.tags dgsub_0_tags_join WHERE dgsub_0.id = o.id AND dgsub_0_tags_join IS NOT NULL))', []],
            ExpressionOperatorEnum::IS_NOT_EMPTY => [ExpressionOperatorEnum::IS_NOT_EMPTY, 'tags', null, 'EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0 INNER JOIN dgsub_0.tags dgsub_0_tags_join WHERE dgsub_0.id = o.id AND dgsub_0_tags_join IS NOT NULL)', []],
        ];
    }

    /**
     * @param list<mixed> $expectedParameterValues
     */
    #[DataProvider('operatorDataProvider')]
    public function testOperatorIsExpressedInDql(
        string $operator,
        string $path,
        mixed $value,
        string $expectedWhere,
        array $expectedParameterValues,
    ): void {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->compile(new Comparison($path, $operator, $value)));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertQuery('SELECT o.id AS id FROM ' . DummyProduct::class . ' o WHERE ' . $expectedWhere, $queryBuilder);
        $this->assertSame($expectedParameterValues, array_map(
            static fn (Parameter $parameter): mixed => $parameter->getValue(),
            $queryBuilder->getParameters()->toArray(),
        ));
    }

    /**
     * `operatorDataProvider()` has to list every operator of the enum, so none can miss its branch in `compare()`.
     */
    public function testEveryOperatorOfTheVocabularyIsExpressed(): void
    {
        $this->assertEqualsCanonicalizing((new ExpressionOperatorEnum())->getAllCases(), array_keys(self::operatorDataProvider()));
    }

    /**
     * An operation a project adds to the enum fails until the builder is extended to express it.
     */
    public function testOperationTheBuilderDoesNotExpressIsRefused(): void
    {
        $expressionOperatorEnum = new class() extends ExpressionOperatorEnum {
            public const string JSON_CONTAINS = 'jsonContains';
        };
        $expressionBuilder = new DqlExpressionBuilder($this->createProxyQuery(DummyProduct::class), $expressionOperatorEnum);

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage('Operator "jsonContains" is not expressed by');

        $expressionBuilder->compile(new Comparison('name', 'jsonContains', 'Foo'));
    }

    private function createExpressionBuilder(ProxyQuery $proxyQuery): DqlExpressionBuilder
    {
        return new DqlExpressionBuilder($proxyQuery, new ExpressionOperatorEnum());
    }

    /**
     * Every query selects the identifier, so that it is valid DQL.
     *
     * @param class-string $entityClass
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        $entityManager = DummyEntityManagerFactory::create();
        $proxyQuery = new ProxyQuery($entityClass, $entityManager, new PathWalker($entityManager), self::LOCALE);
        $proxyQuery->addSelect('id');

        return $proxyQuery;
    }
}
