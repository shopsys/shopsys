<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid\Expression;

use App\Model\Order\Order;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The two media have to answer the same question the same way — a datagrid must list the same records
 * whether they are loaded by a query or filtered in memory.
 *
 * Every case builds one expression twice: the query is executed against the database, the callback is run
 * over the very same orders read into arrays. The two sets of matched identifiers have to be equal.
 */
class ExpressionMediaAgreementTest extends TransactionFunctionalTestCase
{
    /**
     * No path of the compared expressions is translated, so the locale of the query never matters here.
     */
    private const string LOCALE = 'cs';

    /**
     * @return array<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object}>
     */
    public static function expressionProvider(): array
    {
        return array_merge(
            self::getComparisonExpressions(),
            self::getTextExpressions(),
            self::getAssociationExpressions(),
            self::getCompiledConditions(),
        );
    }

    /**
     * The same questions asked as a condition tree compiled by the compiler, so that the translation of
     * conditions arriving as data — the way a filter or a domain control narrows a datagrid — is proven
     * against both media as well.
     *
     * @return array<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object}>
     */
    private static function getCompiledConditions(): array
    {
        $compiler = new ExpressionCompiler(new ExpressionOperatorEnum());

        return [
            'compiled condition: contains through a to-one association' => [
                static fn (ExpressionBuilderInterface $expr): object => $compiler->compile(
                    $expr,
                    new Comparison('customerUser.email', ExpressionOperatorEnum::CONTAINS, 'no-reply'),
                ),
            ],
            'compiled condition: is null on a nullable field' => [
                static fn (ExpressionBuilderInterface $expr): object => $compiler->compile(
                    $expr,
                    new Comparison('companyName', ExpressionOperatorEnum::IS_NULL),
                ),
            ],
            'compiled condition: in a list of values' => [
                static fn (ExpressionBuilderInterface $expr): object => $compiler->compile(
                    $expr,
                    Condition::in('city', ['Ostrava', 'Praha']),
                ),
            ],
            'compiled condition: between two dates' => [
                static fn (ExpressionBuilderInterface $expr): object => $compiler->compile(
                    $expr,
                    Condition::between('createdAt', new DateTimeImmutable('2000-01-01'), new DateTimeImmutable('2100-01-01')),
                ),
            ],
            'compiled condition: groups and negation combined' => [
                static fn (ExpressionBuilderInterface $expr): object => $compiler->compile(
                    $expr,
                    Condition::andX(
                        Condition::isNotNull('email'),
                        Condition::orX(
                            Condition::contains('items.name', 'a'),
                            Condition::not(Condition::isNull('customerUser.email')),
                        ),
                    ),
                ),
            ],
        ];
    }

    /**
     * @return array<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object}>
     */
    private static function getComparisonExpressions(): array
    {
        return [
            'equals on a field of the entity' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->equals('city', 'Ostrava'),
            ],
            'not equals on a field of the entity' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->notEquals('city', 'Ostrava'),
            ],
            'greater than a date' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->greaterThan('createdAt', new DateTimeImmutable('2000-01-01')),
            ],
            'less than a date' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->lessThan('createdAt', new DateTimeImmutable('2000-01-01')),
            ],
            'between two dates' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->between(
                    'createdAt',
                    new DateTimeImmutable('2000-01-01'),
                    new DateTimeImmutable('2100-01-01'),
                ),
            ],
            'in a list of values' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->in('city', ['Ostrava', 'Praha']),
            ],
            'not in a list of values' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->notIn('city', ['Ostrava', 'Praha']),
            ],
            'in an empty list of values' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->in('city', []),
            ],
            'is null on a nullable field' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->isNull('companyName'),
            ],
            'is not null on a nullable field' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->isNotNull('companyName'),
            ],
        ];
    }

    /**
     * @return array<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object}>
     */
    private static function getTextExpressions(): array
    {
        return [
            'contains ignoring case' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->contains('email', 'NO-REPLY'),
            ],
            'contains ignoring diacritics' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->contains('city', 'óstravá'),
            ],
            'contains a literal wildcard' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->contains('email', '%'),
            ],
            'starts with' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->startsWith('number', '1'),
            ],
            'ends with' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->endsWith('number', '1'),
            ],
        ];
    }

    /**
     * @return array<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object}>
     */
    private static function getAssociationExpressions(): array
    {
        return [
            'contains through a to-one association' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->contains('customerUser.email', 'no-reply'),
            ],
            'is null through a to-one association' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->isNull('customerUser.email'),
            ],
            'contains through a to-many association' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->contains('items.name', 'a'),
            ],
            'empty collection' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->isEmpty('items'),
            ],
            'non-empty collection' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->isNotEmpty('items'),
            ],
            'conjunction, disjunction and negation combined' => [
                static fn (ExpressionBuilderInterface $expr): object => $expr->andX(
                    $expr->isNotNull('email'),
                    $expr->orX(
                        $expr->contains('items.name', 'a'),
                        $expr->not($expr->isNull('customerUser.email')),
                    ),
                ),
            ],
        ];
    }

    /**
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object $buildExpression
     */
    #[DataProvider('expressionProvider')]
    public function testBothMediaMatchTheSameOrders(Closure $buildExpression): void
    {
        $orderRows = $this->getOrderRows();
        $this->assertNotEmpty($orderRows, 'The demo data have to contain orders for the comparison to mean anything.');

        $this->assertSame(
            $this->getIdsMatchedByQuery($buildExpression),
            $this->getIdsMatchedInMemory($buildExpression, $orderRows),
        );
    }

    /**
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object $buildExpression
     * @return int[]
     */
    private function getIdsMatchedByQuery(Closure $buildExpression): array
    {
        $proxyQuery = new ProxyQuery(Order::class, $this->em, self::LOCALE);
        $proxyQuery->addSelect('id');
        $proxyQuery->applyExpression($buildExpression((new DqlExpressionBuilderFactory(new ExpressionOperatorApplicability()))->create($proxyQuery)));

        $ids = array_map(
            static fn (array $row): int => (int)$row['id'],
            $proxyQuery->getQueryBuilder()->getQuery()->getScalarResult(),
        );
        sort($ids);

        return $ids;
    }

    /**
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface): object $buildExpression
     * @param array<int, array<string, mixed>> $orderRows
     * @return int[]
     */
    private function getIdsMatchedInMemory(Closure $buildExpression, array $orderRows): array
    {
        $matches = $buildExpression(new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator()));
        $ids = array_map(
            static fn (array $row): int => $row['id'],
            array_filter($orderRows, $matches),
        );
        sort($ids);

        return $ids;
    }

    /**
     * The orders are read into the very same shape the paths of the expressions address, so that both
     * media work with the same data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getOrderRows(): array
    {
        $rows = [];

        /** @var \App\Model\Order\Order $order */
        foreach ($this->em->getRepository(Order::class)->findAll() as $order) {
            $customerUser = $order->getCustomerUser();

            $rows[] = [
                'id' => $order->getId(),
                'number' => $order->getNumber(),
                'email' => $order->getEmail(),
                'city' => $order->getCity(),
                'companyName' => $order->getCompanyName(),
                'createdAt' => $order->getCreatedAt(),
                'customerUser' => $customerUser === null ? null : ['email' => $customerUser->getEmail()],
                'items' => array_map(
                    static fn ($orderItem): array => ['name' => $orderItem->getName()],
                    $order->getItems(),
                ),
            ];
        }

        return $rows;
    }
}
