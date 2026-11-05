<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data;

/**
 * One rule the administrator composed — which filter, which operation, compared with what.
 */
class FilterRuleData
{
    /**
     * @var string|null
     */
    public $filter;

    /**
     * @var string|null One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     */
    public $operator;

    /**
     * @var mixed Shape given by the arity of the operator — null, a single value, a list, or `['from' => ..., 'to' => ...]`
     */
    public $value;
}
