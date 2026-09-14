<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Symfony\Component\Form\FormInterface;

/**
 * The filter of one datagrid — the filters offered to the administrator, the form of the groups of rules composed
 * from them and the prototypes of the rules.
 *
 * Built by `DatagridNarrowingResolver` from the filters and the request state of the datagrid; the component renders
 * the forms, the condition narrows the records.
 */
final readonly class FilterPanel
{
    /**
     * @param \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData> $form
     * @param \Symfony\Component\Form\FormInterface $prototypesForm The prototypes of the rules the page swaps in, never submitted
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The filters offered, already adapted to the medium of the datagrid
     */
    public function __construct(
        public FormInterface $form,
        public FormInterface $prototypesForm,
        public FilterCollection $filters,
    ) {
    }

    /**
     * Whether the administrator composed any rule — a form never submitted, or submitted empty, is only the starting
     * point it offers.
     */
    public function hasRules(): bool
    {
        return $this->form->isSubmitted() && $this->form->getData()->hasRules();
    }

    /**
     * The condition of everything the administrator composed: the rules of a group combined by the operator of
     * the group, the groups by the operator of the form. An invalid form narrows nothing and shows its errors
     * instead of quietly listing more than the administrator asked for; a rule of an unknown filter or without
     * a value narrows nothing either. Null when nothing narrows at all.
     */
    public function createCondition(): ?ConditionInterface
    {
        if ($this->hasRules() === false || $this->form->isValid() === false) {
            return null;
        }

        $data = $this->form->getData();
        $groupConditions = [];

        foreach ($data->groups as $group) {
            $ruleConditions = [];

            foreach ($group->rules as $rule) {
                if ($rule->filter === null || $this->filters->has($rule->filter) === false) {
                    continue;
                }

                $ruleCondition = $this->filters->get($rule->filter)->buildCondition($rule);

                if ($ruleCondition !== null) {
                    $ruleConditions[] = $ruleCondition;
                }
            }

            if ($ruleConditions !== []) {
                $groupConditions[] = $this->compose($group->operator, $ruleConditions);
            }
        }

        return $groupConditions === [] ? null : $this->compose($data->operator, $groupConditions);
    }

    /**
     * @param string|null $operator Null (the operator was not submitted) combines by AND, the way the form starts
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface> $conditions
     */
    private function compose(?string $operator, array $conditions): ConditionInterface
    {
        if (count($conditions) === 1) {
            return $conditions[0];
        }

        return $operator === FilterGroupData::OPERATOR_OR ? Condition::orX(...$conditions) : Condition::andX(...$conditions);
    }
}
