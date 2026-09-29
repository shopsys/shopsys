<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;

/**
 * A filter the administrator composes rules from — one path of the records, the operations offered on it,
 * the form of the value, and the translation of a submitted rule into a condition.
 *
 * A filter is a declaration: it never touches the adapter or a query. Whatever it cannot say through the
 * condition tree is not a filter but a `DqlCondition` returned from `buildCondition()`, with the datagrid
 * bound to the ORM adapter as the price.
 */
interface FilterInterface
{
    /**
     * The key of the filter in the request and in a saved state — stable and explicit, never a label.
     */
    public function getName(): string;

    public function getPath(): string;

    public function getLabel(): string;

    /**
     * The operations offered to the administrator, in the order they are offered — after `resolveFor()`
     * only those the medium supports on the path.
     *
     * @return string[] Cases of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     */
    public function getOperators(): array;

    public function getOperatorLabel(string $operator): string;

    /**
     * The shape of the value the operation takes — usually the arity of the operation in the vocabulary,
     * but a filter may offer operations of its own that carry their value in themselves ("is yes").
     *
     * @return string One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum
     */
    public function getValueArity(string $operator): string;

    /**
     * The form type of the value compared by the operator — for an operation comparing a pair of bounds
     * the type of one bound, the form wraps it into a range itself. Not asked for an operation without a value.
     *
     * @return class-string<\Symfony\Component\Form\FormTypeInterface>
     */
    public function getValueFormType(string $operator): string;

    /**
     * @return array<string, mixed>
     */
    public function getValueFormOptions(string $operator): array;

    /**
     * Adapts the filter to the datagrid it is declared on: learns the path, keeps only the operations the
     * medium can evaluate on it, and refuses a declaration that cannot work at all.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function resolveFor(FilterEnvironment $environment): void;

    /**
     * The condition of one submitted rule, null when the rule narrows nothing (no value given).
     */
    public function buildCondition(FilterRuleData $rule): ?ConditionInterface;
}
