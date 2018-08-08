<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchFacade
{
    const RULES_FORM_NAME = 'as';

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchFormFactory
     */
    protected $advancedSearchFormFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchService
     */
    protected $advancedSearchService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade
     */
    protected $productListAdminFacade;

    public function __construct(
        ProductAdvancedSearchFormFactory $advancedSearchFormFactory,
        AdvancedSearchService $advancedSearchService,
        ProductListAdminFacade $productListAdminFacade
    ) {
        $this->advancedSearchFormFactory = $advancedSearchFormFactory;
        $this->advancedSearchService = $advancedSearchService;
        $this->productListAdminFacade = $productListAdminFacade;
    }

    public function createAdvancedSearchForm(Request $request): \Symfony\Component\Form\FormInterface
    {
        $rulesData = (array)$request->get(self::RULES_FORM_NAME);
        $rulesFormData = $this->advancedSearchService->getRulesFormViewDataByRequestData($rulesData);

        return $this->advancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesFormData);
    }

    /**
     * @param string|int $index
     */
    public function createRuleForm(string $filterName, $index): \Symfony\Component\Form\FormInterface
    {
        $rulesData = [
            $index => $this->advancedSearchService->createDefaultRuleFormViewData($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesData);
    }
    
    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->productListAdminFacade->getProductListQueryBuilder();
        $this->advancedSearchService->extendQueryBuilderByAdvancedSearchData($queryBuilder, $advancedSearchData);

        return $queryBuilder;
    }

    public function isAdvancedSearchFormSubmitted(Request $request): bool
    {
        $rulesData = $request->get(self::RULES_FORM_NAME);

        return $rulesData !== null;
    }
}
