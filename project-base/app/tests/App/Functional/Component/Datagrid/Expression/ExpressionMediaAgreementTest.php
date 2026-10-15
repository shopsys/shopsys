<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid\Expression;

use App\Model\Order\Order;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The two media have to answer the same question the same way — a datagrid must list the same records
 * whether they are loaded by a query or filtered in memory.
 *
 * Every condition is compiled by both media: the query is executed against the database, the callback is run
 * over the very same orders read into arrays. The two sets of matched identifiers have to be equal.
 */
class ExpressionMediaAgreementTest extends TransactionFunctionalTestCase
{
    /**
     * No path of the compared expressions is translated, so the locale of the query never matters here.
     */
    private const string LOCALE = 'cs';

    /**
     * @return array<string, array{\Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface}>
     */
    public static function conditionProvider(): array
    {
        return [
            'equals on a field of the entity' => [Condition::equals('city', 'Ostrava')],
            'not equals on a field of the entity' => [Condition::notEquals('city', 'Ostrava')],
            'greater than a date' => [Condition::greaterThan('createdAt', new DateTimeImmutable('2000-01-01'))],
            'less than a date' => [Condition::lessThan('createdAt', new DateTimeImmutable('2000-01-01'))],
            'between two dates' => [Condition::between('createdAt', new DateTimeImmutable('2000-01-01'), new DateTimeImmutable('2100-01-01'))],
            'in a list of values' => [Condition::in('city', ['Ostrava', 'Praha'])],
            'not in a list of values' => [Condition::notIn('city', ['Ostrava', 'Praha'])],
            'in an empty list of values' => [Condition::in('city', [])],
            'is null on a nullable field' => [Condition::isNull('companyName')],
            'is not null on a nullable field' => [Condition::isNotNull('companyName')],
            'contains ignoring case' => [Condition::contains('email', 'NO-REPLY')],
            'contains ignoring diacritics' => [Condition::contains('city', 'óstravá')],
            'contains a literal wildcard' => [Condition::contains('email', '%')],
            'starts with' => [Condition::startsWith('number', '1')],
            'ends with' => [Condition::endsWith('number', '1')],
            'contains through a to-one association' => [Condition::contains('customerUser.email', 'no-reply')],
            'is null through a to-one association' => [Condition::isNull('customerUser.email')],
            'contains through a to-many association' => [Condition::contains('items.name', 'a')],
            'empty collection' => [Condition::isEmpty('items')],
            'non-empty collection' => [Condition::isNotEmpty('items')],
            'conjunction, disjunction and negation combined' => [Condition::andX(
                Condition::isNotNull('email'),
                Condition::orX(
                    Condition::contains('items.name', 'a'),
                    Condition::not(Condition::isNull('customerUser.email')),
                ),
            )],
            // a record whose value is missing never matches a comparison, but always matches its negation
            'not equals on a field missing in some orders' => [Condition::notEquals('companyName', 'Shopsys')],
            'not in on a field missing in some orders' => [Condition::notIn('companyName', ['Shopsys'])],
            'negated equality on a field missing in some orders' => [Condition::not(Condition::equals('companyName', 'Shopsys'))],
            'negated search through an association missing in some orders' => [Condition::not(Condition::contains('customerUser.email', 'no-reply'))],
            'negated search through a to-many association' => [Condition::not(Condition::contains('items.name', 'a'))],
        ];
    }

    #[DataProvider('conditionProvider')]
    public function testBothMediaMatchTheSameOrders(ConditionInterface $condition): void
    {
        $orderRows = $this->getOrderRows();
        $this->assertNotEmpty($orderRows, 'The demo data have to contain orders for the comparison to mean anything.');

        $this->assertSame(
            $this->getIdsMatchedByQuery($condition),
            $this->getIdsMatchedInMemory($condition, $orderRows),
        );
    }

    /**
     * @return int[]
     */
    private function getIdsMatchedByQuery(ConditionInterface $condition): array
    {
        $proxyQuery = new ProxyQuery(Order::class, $this->em, new PathWalker($this->em), self::LOCALE);
        $proxyQuery->addSelect('id');
        $proxyQuery->applyExpression((new DqlExpressionBuilderFactory(new ExpressionOperatorEnum()))->create($proxyQuery)->compile($condition));

        $ids = array_map(
            static fn (array $row): int => (int)$row['id'],
            $proxyQuery->getQueryBuilder()->getQuery()->getScalarResult(),
        );
        sort($ids);

        return $ids;
    }

    /**
     * @param array<int, array<string, mixed>> $orderRows
     * @return int[]
     */
    private function getIdsMatchedInMemory(ConditionInterface $condition, array $orderRows): array
    {
        $matches = (new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), new ExpressionOperatorEnum()))->compile($condition);
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
