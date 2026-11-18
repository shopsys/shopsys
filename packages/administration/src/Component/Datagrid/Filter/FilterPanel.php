<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterGroupData;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;
use Symfony\Component\Form\FormInterface;

/**
 * The filter of one datagrid: the filters offered to the administrator and the form of the groups of rules composed
 * from them, already submitted.
 */
final readonly class FilterPanel implements DatagridSearchInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData> $form
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The filters offered, already resolved against the adapter of the datagrid
     */
    public function __construct(
        public FormInterface $form,
        public FilterCollection $filters,
    ) {
    }

    /**
     * Whether the administrator composed any rule. A form never submitted, or submitted empty, is only the starting
     * point it offers.
     */
    #[Override]
    public function isActive(): bool
    {
        return $this->form->isSubmitted() && $this->form->getData()->hasRules();
    }

    /**
     * `isActive()` goes first, as `isValid()` throws for a form never submitted.
     */
    public function isInvalid(): bool
    {
        return $this->isActive() && $this->form->isValid() === false;
    }

    /**
     * The condition of everything the administrator composed, null when nothing narrows at all:
     *
     * - the rules of a group are combined by the operator of the group;
     * - the groups are combined by the operator of the form;
     * - a rule without a filter or without a value is skipped;
     * - an invalid form narrows nothing, so it shows its errors instead of quietly listing more than
     *   the administrator asked for.
     */
    #[Override]
    public function createCondition(): ?ConditionInterface
    {
        if ($this->isActive() === false || $this->form->isValid() === false) {
            return null;
        }

        $data = $this->form->getData();
        $groupConditions = [];

        foreach ($data->groups as $group) {
            $ruleConditions = [];

            foreach ($group->rules as $rule) {
                if ($rule->filter === null) {
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
