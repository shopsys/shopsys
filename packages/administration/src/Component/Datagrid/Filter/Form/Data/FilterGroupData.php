<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data;

/**
 * Rules combined by one logical operator.
 */
class FilterGroupData
{
    public const string OPERATOR_AND = 'and';
    public const string OPERATOR_OR = 'or';

    /**
     * @var string
     */
    public $operator = self::OPERATOR_AND;

    /**
     * @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData[]
     */
    public $rules = [];
}
