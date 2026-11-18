<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data;

/**
 * Everything the administrator composed in the filter form — groups of rules, and the logical operator
 * between the groups.
 */
final class FilterFormData
{
    /**
     * @var string
     */
    public $operator = FilterGroupData::OPERATOR_AND;

    /**
     * @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData[]
     */
    public $groups = [];

    /**
     * The form the administrator starts from — one group with one rule waiting to be filled, so that the
     * first rule is one click away.
     */
    public static function createStartingPoint(): self
    {
        $group = new FilterGroupData();
        $group->rules[] = new FilterRuleData();

        $data = new self();
        $data->groups[] = $group;

        return $data;
    }

    public function hasRules(): bool
    {
        foreach ($this->groups as $group) {
            if ($group->rules !== []) {
                return true;
            }
        }

        return false;
    }
}
