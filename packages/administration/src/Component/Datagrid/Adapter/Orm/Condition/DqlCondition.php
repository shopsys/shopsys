<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition;

use Closure;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\MediumSpecificConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Stringable;

/**
 * A condition said in DQL directly — the sanctioned way down from the vocabulary for what it cannot
 * express: an aggregate ("orders with more than three items"), a computed value, a `HAVING` clause.
 *
 *     new DqlCondition(
 *         static fn (QueryBuilder $queryBuilder, ProxyQuery $proxyQuery): string
 *             => sprintf('SIZE(o.items) > :%s', $proxyQuery->addParameter(3)),
 *     )
 *
 * It composes with the rest of the tree, but the datagrid then works with the ORM adapter only.
 */
final readonly class DqlCondition implements MediumSpecificConditionInterface
{
    /**
     * @param \Closure(\Doctrine\ORM\QueryBuilder, \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery): string $buildPredicate Returns the DQL predicate, see `DqlExpressionBuilderInterface::dql()`
     */
    public function __construct(
        public Closure $buildPredicate,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function compile(ExpressionBuilderInterface $expressionBuilder): Stringable
    {
        if ($expressionBuilder instanceof DqlExpressionBuilderInterface === false) {
            throw new ConditionNotSupportedException(self::class, $expressionBuilder::class);
        }

        return $expressionBuilder->dql($this->buildPredicate);
    }
}
