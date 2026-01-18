<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;

class AdvancedSearchQueryBuilderExtender
{
    public function __construct(protected readonly AdvancedSearchConfig $advancedSearchConfig)
    {
    }

    /**
     * @param array<int|string, \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData> $advancedSearchData
     */
    public function extendByAdvancedSearchData(QueryBuilder $queryBuilder, array $advancedSearchData): void
    {
        $rulesDataByFilterName = [];

        foreach ($advancedSearchData as $key => $ruleData) {
            if ($key === RuleFormViewDataFactory::TEMPLATE_RULE_FORM_KEY || $ruleData->operator === null) {
                continue;
            }
            $rulesDataByFilterName[$ruleData->subject][] = $ruleData;
        }

        foreach ($rulesDataByFilterName as $filterName => $rulesData) {
            $filter = $this->advancedSearchConfig->getFilter($filterName);
            $filter->extendQueryBuilder($queryBuilder, $rulesData);
        }
    }
}
