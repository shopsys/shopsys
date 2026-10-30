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
 * The filter of one datagrid — the filters offered to the administrator and the form of the groups of rules composed
 * from them.
 *
 * A part of the state of the datagrid added by `FilterStateSubscriber`, which submits the form once — the condition
 * narrows the records before they are loaded, the component renders the very same form with its errors.
 */
final readonly class FilterPanel implements DatagridSearchInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData> $form
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $filters The filters offered, already adapted to the medium of the datagrid
     */
    public function __construct(
        public FormInterface $form,
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
     * Any composed rule outranks the quick search, even an invalid one.
     */
    #[Override]
    public function isActive(): bool
    {
        return $this->hasRules();
    }

    /**
     * Whether the composed rules cannot be applied — the form is never valid unless submitted.
     */
    public function isInvalid(): bool
    {
        return $this->hasRules() && $this->form->isValid() === false;
    }

    /**
     * The condition of everything the administrator composed: the rules of a group combined by the operator of
     * the group, the groups by the operator of the form. An invalid form narrows nothing and shows its errors
     * instead of quietly listing more than the administrator asked for; a rule of an unknown filter or without
     * a value narrows nothing either. Null when nothing narrows at all.
     */
    #[Override]
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
