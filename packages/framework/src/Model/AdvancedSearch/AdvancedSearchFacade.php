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

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAdvancedSearchForm(Request $request)
    {
        $rulesData = (array)$request->get(self::RULES_FORM_NAME);
        $rulesFormData = $this->advancedSearchService->getRulesFormViewDataByRequestData($rulesData);

        return $this->advancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesFormData);
    }

    /**
     * @param string $filterName
     * @param string|int $index
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRuleForm($filterName, $index)
    {
        $rulesData = [
            $index => $this->advancedSearchService->createDefaultRuleFormViewData($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesData);
    }

    /**
     * @param array $advancedSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByAdvancedSearchData($advancedSearchData)
    {
        $queryBuilder = $this->productListAdminFacade->getProductListQueryBuilder();
        $this->advancedSearchService->extendQueryBuilderByAdvancedSearchData($queryBuilder, $advancedSearchData);

        return $queryBuilder;
    }

    /**
     * @return bool
     */
    public function isAdvancedSearchFormSubmitted(Request $request)
    {
        $rulesData = $request->get(self::RULES_FORM_NAME);

        return $rulesData !== null;
    }
}
