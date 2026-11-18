<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;

trait FilterRuleTrait
{
    private function createRule(string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}
