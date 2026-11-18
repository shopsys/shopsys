<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data;

/**
 * One rule the administrator composed.
 */
final class FilterRuleData
{
    /**
     * @var string|null
     */
    public $filter;

    /**
     * @var string|null An operation of the filter, usually a constant of `ExpressionOperatorEnum` — a filter may add its own (`BooleanFilter::OPERATOR_YES`)
     */
    public $operator;

    /**
     * @var mixed Shape given by the arity of the operator — null, a single value, a list, or `['from' => ..., 'to' => ...]`
     */
    public $value;
}
