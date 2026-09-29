<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Expression;

use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Doctrine\NormalizedFunction;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyArticle;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyShadowedArticle;

final class DqlExpressionBuilderTest extends TestCase
{
    private const string LOCALE = 'cs';

    public function testExpressionOnRootFieldIsAppliedWithParameter(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('name', 'Foo'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('o.name = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('Foo', $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    public function testExpressionOnAssociationPathJoinsTheAssociation(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->contains('currency.code', 'CZ'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('NORMALIZED(currency_join.code) LIKE NORMALIZED(:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionOnDeepAssociationPathJoinsEveryAssociationOnTheWay(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('currency.format.pattern', '#'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $dql);
        $this->assertStringContainsString('LEFT JOIN currency_join.format currency__format_join', $dql);
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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->isNull('warehouse.name'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN o.warehouse warehouse_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('warehouse_join.name IS NULL', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('LEFT JOIN', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionSharesTheJoinWithSelectOfTheSamePath(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('currency.code');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('currency.code', 'CZK'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(1, substr_count($queryBuilder->getDQL(), 'LEFT JOIN'));
        $this->assertStringContainsString('currency_join.code = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
    }

    public function testTwoExpressionsOnTheSamePathShareTheJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->equals('currency.code', 'CZK'));
        $proxyQuery->applyExpression($expressionBuilder->notEquals('currency.code', 'EUR'));

        $this->assertSame(1, substr_count($proxyQuery->getQueryBuilder()->getDQL(), 'LEFT JOIN'));
    }

    public function testExpressionOnTranslatedFieldUsesTheLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyArticle::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('name', 'Foo'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertSame(1, substr_count($queryBuilder->getDQL(), 'LEFT JOIN o.translations o_tr'));
        $this->assertStringContainsString('o_tr.name = :dgexpr_0', (string)$queryBuilder->getDQLPart('where'));
    }

    public function testExpressionOnExplicitlyAddressedTranslatedFieldUsesTheLocaleJoin(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyShadowedArticle::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('translations.name', 'Foo'));

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

        $proxyQuery->applyExpression($expressionBuilder->equals('id', 1));
        $proxyQuery->applyExpression($expressionBuilder->between('id', 5, 10));

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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->greaterThan('createdAt', $createdAt));

        $parameter = $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0');
        $this->assertSame($createdAt, $parameter->getValue());
        $this->assertSame('datetime_immutable', $parameter->getType());
        $this->assertStringContainsString('SELECT', $proxyQuery->getQueryBuilder()->getQuery()->getSQL());
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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->contains('name', $value));

        $this->assertSame($expectedPattern, $proxyQuery->getQueryBuilder()->getParameter('dgexpr_0')->getValue());
    }

    /**
     * Both sides are normalized by the database, so the value is bound as typed and the case and the
     * diacritics are settled under one definition.
     */
    public function testSearchedValueIsNormalizedByTheDatabase(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->startsWith('name', 'FOO'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('FOO%', $queryBuilder->getParameter('dgexpr_0')->getValue());
    }

    public function testEmptyListOfValuesMatchesNothing(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->in('id', []));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('1 = 0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testEmptyListOfExcludedValuesMatchesEverything(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->notIn('id', []));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('1 = 1', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testExpressionOnOneToManyPathIsMatchedBySubquery(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->in('domains.domainId', [2]));

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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('tags.name', 'sale'));

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

        $proxyQuery->applyExpression($expressionBuilder->in('domains.domainId', [2]));
        $proxyQuery->applyExpression($expressionBuilder->equals('tags.name', 'sale'));

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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('labels.name', 'Sale'));

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

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->equals('domains.domainId', 2));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $this->assertStringContainsString('LEFT JOIN o.currency currency_join', $queryBuilder->getDQL());
        $this->assertStringContainsString('dgsub_0_domains_join', (string)$queryBuilder->getDQLPart('where'));
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testExpressionsAreCombinedByConjunction(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');
        $expressionBuilder = $this->createExpressionBuilder($proxyQuery);

        $proxyQuery->applyExpression($expressionBuilder->andX(
            $expressionBuilder->equals('name', 'Foo'),
            $expressionBuilder->orX(
                $expressionBuilder->equals('id', 1),
                $expressionBuilder->not($expressionBuilder->isNull('warehouse.name')),
            ),
        ));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringContainsString('o.name = :dgexpr_0 AND (o.id = :dgexpr_1 OR NOT (warehouse_join.name IS NULL))', $where);
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * Negating a whole expression asks something else than the negating operation of that expression,
     * once the path leads through a to-many association.
     */
    public function testNegatingAnExpressionOverToManyPathDiffersFromTheNegatingOperation(): void
    {
        $negatedQuery = $this->createProxyQuery(DummyProduct::class);
        $negatedQuery->applyExpression($this->createExpressionBuilder($negatedQuery)->not($this->createExpressionBuilder($negatedQuery)->equals('tags.name', 'sale')));

        $negatingQuery = $this->createProxyQuery(DummyProduct::class);
        $negatingQuery->applyExpression($this->createExpressionBuilder($negatingQuery)->notEquals('tags.name', 'sale'));

        // no related row is named sale
        $this->assertStringStartsWith('NOT (EXISTS(', (string)$negatedQuery->getQueryBuilder()->getDQLPart('where'));
        // some related row is not named sale
        $negatingWhere = (string)$negatingQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringStartsWith('EXISTS(', $negatingWhere);
        $this->assertStringContainsString('name <> :dgexpr_0', $negatingWhere);
    }

    public function testEmptinessOfCollectionIsMatchedBySubqueryWithoutPredicate(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->isEmpty('tags'));

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $where = (string)$queryBuilder->getDQLPart('where');
        $this->assertStringStartsWith('NOT (EXISTS(SELECT 1 FROM ' . DummyProduct::class . ' dgsub_0 INNER JOIN dgsub_0.tags dgsub_0_tags_join WHERE dgsub_0.id = o.id))', $where);
        $this->assertCount(0, $queryBuilder->getParameters());
        $this->assertStringContainsString('NOT (EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    public function testNonEmptinessOfCollectionIsMatchedBySubqueryWithoutPredicate(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);
        $proxyQuery->addSelect('id');

        $proxyQuery->applyExpression($this->createExpressionBuilder($proxyQuery)->isNotEmpty('domains'));

        $where = (string)$proxyQuery->getQueryBuilder()->getDQLPart('where');
        $this->assertStringStartsWith('EXISTS(SELECT 1 FROM', $where);
        $this->assertStringContainsString('INNER JOIN dgsub_0.domains dgsub_0_domains_join WHERE dgsub_0.id = o.id)', $where);
    }

    /**
     * A text search on a date fails in the database, so it is refused with a reason instead.
     */
    public function testTextOperationOnNonTextPathIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('Operator "contains" cannot be applied to path "createdAt"');

        $this->createExpressionBuilder($proxyQuery)->contains('createdAt', '2026');
    }

    /**
     * The subquery joins the related rows inwardly, so a null test on a collection could never match.
     */
    public function testNullTestOnCollectionIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('use isEmpty or isNotEmpty');

        $this->createExpressionBuilder($proxyQuery)->isNull('tags.name');
    }

    public function testEmptinessTestOnSingleValueIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(OperatorNotApplicableToPathException::class);
        $this->expectExceptionMessage('only a collection can be empty');

        $this->createExpressionBuilder($proxyQuery)->isEmpty('name');
    }

    public function testNoExpressionAtAllMatchesEverything(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->assertSame('1 = 1', (string)$this->createExpressionBuilder($proxyQuery)->andX());
        $this->assertSame('1 = 0', (string)$this->createExpressionBuilder($proxyQuery)->orX());
    }

    public function testExpressionOfAnotherMediumIsRefused(): void
    {
        $proxyQuery = $this->createProxyQuery(DummyProduct::class);

        $this->expectException(ExpressionFromForeignMediumException::class);

        // the static analysis refuses this as well, which is the first line of defence
        /** @phpstan-ignore argument.type */
        $this->createExpressionBuilder($proxyQuery)->not(static fn (): bool => true);
    }

    public function testEveryOperatorOfTheVocabularyIsSupported(): void
    {
        $expressionBuilder = $this->createExpressionBuilder($this->createProxyQuery(DummyProduct::class));

        foreach ((new ExpressionOperatorEnum())->getAllCases() as $operator) {
            $this->assertTrue($expressionBuilder->supportsOperator($operator), $operator);
        }
    }

    private function createExpressionBuilder(ProxyQuery $proxyQuery): DqlExpressionBuilderInterface
    {
        return (new DqlExpressionBuilderFactory(new ExpressionOperatorApplicability()))->create($proxyQuery);
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
        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/../Fixture'], true);
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
