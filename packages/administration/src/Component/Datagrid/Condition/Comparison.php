<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * One operation of the vocabulary on one path — `email contains "novak"`, `createdAt between [from, to]`,
 * `images isEmpty`.
 *
 * The operator is a case of `ExpressionOperatorEnum` and the shape of the value is given by its arity
 * (`ExpressionValueArityEnum`): null, a single value, a list, or a pair of bounds. The shape is checked when
 * the comparison is compiled, so that a condition arriving as data from a request is validated in one place
 * for every medium. The named constructors of `Condition` give the typed signature per operation.
 */
final readonly class Comparison implements ConditionInterface
{
    /**
     * @param string $path Dot notation path, the same one datagrid fields use — `email`, `customerUser.email`
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     * @param mixed $value Shape given by the arity of the operator
     */
    public function __construct(
        public string $path,
        public string $operator,
        public mixed $value = null,
    ) {
    }
}
