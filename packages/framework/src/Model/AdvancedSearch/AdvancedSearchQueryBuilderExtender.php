<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry;

class AdvancedSearchQueryBuilderExtender
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry $advancedSearchFilterRegistry
     */
    public function __construct(protected readonly AdvancedSearchFilterRegistry $advancedSearchFilterRegistry)
    {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param array<\Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData|null> $advancedSearchData
     * @param string $entityType
     */
    public function extendByAdvancedSearchData(
        QueryBuilder $queryBuilder,
        array $advancedSearchData,
        string $entityType,
    ) {
        $rulesDataByFilterName = [];

        foreach ($advancedSearchData as $key => $ruleData) {
            if ($ruleData === null || $key === RuleFormViewDataFactory::TEMPLATE_RULE_FORM_KEY || $ruleData->operator === null) {
                continue;
            }
            $rulesDataByFilterName[$ruleData->subject][] = $ruleData;
        }

        foreach ($rulesDataByFilterName as $filterName => $rulesData) {
            $filter = $this->advancedSearchFilterRegistry->getFilter($entityType, $filterName);
            $filter->extendQueryBuilder($queryBuilder, $rulesData);
        }
    }
}
