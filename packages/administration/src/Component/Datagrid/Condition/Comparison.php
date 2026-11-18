<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * One operation of the vocabulary on one path — `email contains "novak"`, `createdAt between [from, to]`,
 * `images isEmpty`.
 *
 * The value has the shape of its arity (`ExpressionValueArityEnum`): null, a single value, a list, or a pair of bounds.
 * The shape is checked when the comparison is compiled.
 * The named constructors of `Condition` give the typed signature per operation.
 */
final readonly class Comparison implements ConditionInterface
{
    /**
     * @param string $path Dot notation path, the same one datagrid fields use — `email`, `customerUser.email`
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     */
    public function __construct(
        public string $path,
        public string $operator,
        public mixed $value = null,
    ) {
    }
}
