<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition;

use Closure;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * A condition written in DQL directly, for what the operations cannot express:
 *
 * - an aggregate of the related rows — "orders with more than three items";
 * - a computed value.
 *
 *     new DqlCondition(
 *         static fn (ProxyQuery $proxyQuery): string
 *             => sprintf('SIZE(%s.items) > :%s', ProxyQuery::DEFAULT_ALIAS, $proxyQuery->addParameter(3)),
 *     )
 *
 * The predicate goes to the `WHERE` clause, the query has no `GROUP BY` a `HAVING` clause could narrow.
 * It composes with the rest of the tree, but only `DqlExpressionBuilder` compiles it — the datagrid then works with
 * the ORM adapter only.
 */
final readonly class DqlCondition implements ConditionInterface
{
    /**
     * The closure receives the query of the datagrid:
     *
     * - register every value by `ProxyQuery::addParameter()`, so the query stays parameterized;
     * - resolve every path by `ProxyQuery::resolvePath()`, so the joins are shared with the rest of the datagrid;
     * - match a path through a to-many association by `ProxyQuery::exists()`;
     * - reach the query builder itself by `ProxyQuery::getQueryBuilder()`.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery): string $buildPredicate Returns the DQL predicate
     */
    public function __construct(
        public Closure $buildPredicate,
    ) {
    }
}
