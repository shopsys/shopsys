<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchProductFacade
{
    public const RULES_FORM_NAME = 'as';

    public function __construct(
        protected readonly ProductAdvancedSearchFormFactory $advancedSearchFormFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly ProductListAdminFacade $productListAdminFacade,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAdvancedSearchForm(Request $request)
    {
        $rulesData = $request->query->all(static::RULES_FORM_NAME);
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(ProductNameFilter::NAME, $rulesData);

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData);
    }

    /**
     * @param string $filterName
     * @param string|int $index
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRuleForm($filterName, $index)
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    /**
     * @param array $advancedSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByAdvancedSearchData($advancedSearchData)
    {
        $queryBuilder = $this->productListAdminFacade->getProductListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData);

        return $queryBuilder;
    }

    /**
     * @return bool
     */
    public function isAdvancedSearchFormSubmitted(Request $request)
    {
        return $request->query->has(static::RULES_FORM_NAME);
    }
}
