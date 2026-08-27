<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

final class AdvancedSearchApplier
{
    private const string PARAMETER_PREFIX = 'advancedSearch';

    /**
     * Extends the list query with the conditions of all valid rules of the submitted advanced search form.
     * Rules are grouped by subject and each filter is called once with all its rules.
     * Problems reported by the filters via FilterRuleCollection::addRuleError() are added as form errors on the rule rows.
     */
    public function apply(SearchConfig $searchConfig, QueryBuilder $queryBuilder, FormInterface $rulesForm): void
    {
        $rulesByFilterName = $this->groupRulesByFilterName($searchConfig, $rulesForm->getData() ?? []);

        foreach ($rulesByFilterName as $filterName => $rules) {
            $ruleCollection = new FilterRuleCollection($rules);
            $searchConfig->getFilter($filterName)->extendQueryBuilder($queryBuilder, $ruleCollection);

            $this->addRuleErrorsToForm($rulesForm, $ruleCollection);
        }
    }

    /**
     * @param array<int|string, mixed> $rulesData
     * @return array<string, \Shopsys\AdministrationBundle\Component\Search\FilterRule[]>
     */
    private function groupRulesByFilterName(SearchConfig $searchConfig, array $rulesData): array
    {
        $rulesByFilterName = [];

        foreach ($rulesData as $ruleIndex => $ruleData) {
            if ($ruleIndex === AdvancedSearchFormFactory::TEMPLATE_RULE_KEY || !is_array($ruleData)) {
                continue;
            }

            $filter = $searchConfig->getFilter((string)($ruleData['subject'] ?? ''));
            $operator = Operator::tryFrom((string)($ruleData['operator'] ?? ''));

            if ($filter === null || $operator === null || !in_array($operator, $filter->getAllowedOperators(), true)) {
                continue;
            }

            $value = $ruleData['value'] ?? null;

            if ($operator->hasValue() && ($value === null || $value === '')) {
                continue;
            }

            $rulesByFilterName[$filter->getName()][] = new FilterRule(
                $operator,
                $value,
                (string)$ruleIndex,
                self::PARAMETER_PREFIX . '_' . $filter->getName(),
            );
        }

        return $rulesByFilterName;
    }

    private function addRuleErrorsToForm(FormInterface $rulesForm, FilterRuleCollection $ruleCollection): void
    {
        foreach ($ruleCollection->getRuleErrors() as $formIndex => $errorMessages) {
            if (!$rulesForm->has($formIndex)) {
                continue;
            }

            $ruleForm = $rulesForm->get($formIndex);
            $errorTarget = $ruleForm->has('value') ? $ruleForm->get('value') : $ruleForm;

            foreach ($errorMessages as $errorMessage) {
                $errorTarget->addError(new FormError($errorMessage));
            }
        }
    }
}
