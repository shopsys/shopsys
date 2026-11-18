<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;

/**
 * A filter the administrator composes rules from:
 *
 * - one path of the records;
 * - the operations offered on it;
 * - the form of the value;
 * - the translation of a submitted rule into a condition.
 *
 * A filter never builds a query, it returns a condition. What the operations cannot express, `buildCondition()`
 * may return as a `DqlCondition`, which works with the ORM adapter only.
 */
interface FilterInterface
{
    /**
     * The key of the filter in the request — stable and explicit, never a label.
     */
    public function getName(): string;

    public function getPath(): string;

    public function getLabel(): string;

    /**
     * The operations offered to the administrator, in the order they are offered — after `resolveFor()`
     * only those applicable to the path.
     *
     * @return string[] Usually constants of `ExpressionOperatorEnum`, a filter may add its own (`BooleanFilter::OPERATOR_YES`)
     */
    public function getOperators(): array;

    public function getOperatorLabel(string $operator): string;

    /**
     * The shape of the value the operation takes, usually its arity in `ExpressionOperatorEnum`.
     * An operation of the filter itself may carry its value in itself ("yes" of `BooleanFilter`).
     */
    public function getValueArity(string $operator): ExpressionValueArityEnum;

    /**
     * The form type of the value of the operations of the arity.
     * For a pair of bounds it is the type of one bound, the form wraps it into a range.
     * It is never asked for the arity `NONE`.
     *
     * @return class-string<\Symfony\Component\Form\FormTypeInterface>
     */
    public function getValueFormType(ExpressionValueArityEnum $arity): string;

    /**
     * @return array<string, mixed>
     */
    public function getValueFormOptions(ExpressionValueArityEnum $arity): array;

    /**
     * Resolves the filter against the adapter of its datagrid:
     *
     * - describes the path, when the adapter can;
     * - keeps only the operations applicable to the path;
     * - fails when the filter cannot work over the path.
     */
    public function resolveFor(FilterEnvironment $environment): void;

    /**
     * The condition of one submitted rule, null when the rule narrows nothing (no value given).
     */
    public function buildCondition(FilterRuleData $rule): ?ConditionInterface;
}
