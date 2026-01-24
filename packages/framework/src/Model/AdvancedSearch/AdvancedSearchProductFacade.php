<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Symfony\Component\Form\FormInterface;
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

    public function createAdvancedSearchForm(Request $request): FormInterface
    {
        $rawRulesData = $request->get(static::RULES_FORM_NAME);
        $rulesData = is_array($rawRulesData) ? $rawRulesData : [];
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(ProductNameFilter::NAME, $rulesData);

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData);
    }

    public function createRuleForm(string $filterName, string $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): QueryBuilder
    {
        $queryBuilder = $this->productListAdminFacade->getProductListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData);

        return $queryBuilder;
    }

    public function isAdvancedSearchFormSubmitted(Request $request): bool
    {
        $rulesData = $request->get(static::RULES_FORM_NAME);

        return $rulesData !== null;
    }
}
