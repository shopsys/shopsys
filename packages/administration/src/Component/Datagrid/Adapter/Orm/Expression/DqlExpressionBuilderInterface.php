<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Closure;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Stringable;

/**
 * The builder of the DQL medium — the shared vocabulary plus the one door down to the query itself.
 *
 * A project wrapping the default builder implements this interface, so that `DqlCondition` keeps working
 * with the wrapped one.
 *
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<\Stringable>
 */
interface DqlExpressionBuilderInterface extends ExpressionBuilderInterface
{
    /**
     * Builds a predicate said in DQL directly, for what the vocabulary cannot express — an aggregate,
     * a computed value.
     *
     * Use `ProxyQuery::addParameter()` for every value and `ProxyQuery::resolvePath()` for every path, so that
     * the query stays parameterized and the joins stay shared with the rest of the datagrid.
     *
     * @param \Closure(\Doctrine\ORM\QueryBuilder, \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery): string $buildPredicate Returns the DQL predicate
     */
    public function dql(Closure $buildPredicate): Stringable;
}
