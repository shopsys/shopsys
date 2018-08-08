<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;

class AdvancedSearchService
{
    const TEMPLATE_RULE_FORM_KEY = '__template__';

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig
     */
    private $advancedSearchConfig;

    public function __construct(ProductAdvancedSearchConfig $advancedSearchConfig)
    {
        $this->advancedSearchConfig = $advancedSearchConfig;
    }

    /**
     * @param array|null $requestData
     */
    public function getRulesFormViewDataByRequestData(array $requestData = null): array
    {
        if ($requestData === null) {
            $searchRulesViewData = [];
        } else {
            $searchRulesViewData = array_values($requestData);
        }

        if (count($searchRulesViewData) === 0) {
            $searchRulesViewData[] = $this->createDefaultRuleFormViewData('productName');
        }

        $searchRulesViewData[self::TEMPLATE_RULE_FORM_KEY] = $this->createDefaultRuleFormViewData('productName');

        return $searchRulesViewData;
    }
    
    public function createDefaultRuleFormViewData(string $filterName): array
    {
        return [
            'subject' => $filterName,
            'operator' => null,
            'value' => null,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $advancedSearchData
     */
    public function extendQueryBuilderByAdvancedSearchData(QueryBuilder $queryBuilder, array $advancedSearchData): void
    {
        $rulesDataByFilterName = [];
        foreach ($advancedSearchData as $key => $ruleData) {
            if ($key === self::TEMPLATE_RULE_FORM_KEY || $ruleData->operator === null) {
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
