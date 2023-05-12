<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;

class AdvancedSearchQueryBuilderExtender
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig $advancedSearchConfig
     */
    public function __construct(protected readonly AdvancedSearchConfig $advancedSearchConfig)
    {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $advancedSearchData
     */
    public function extendByAdvancedSearchData(QueryBuilder $queryBuilder, array $advancedSearchData)
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
