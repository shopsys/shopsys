<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchOrderFacade
{
    const RULES_FORM_NAME = 'as';

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchFormFactory
     */
    protected $orderAdvancedSearchFormFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderService
     */
    protected $advancedSearchOrderService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade
     */
    protected $orderListAdminFacade;

    public function __construct(
        OrderAdvancedSearchFormFactory $orderAdvancedSearchFormFactory,
        AdvancedSearchOrderService $advancedSearchOrderService,
        OrderListAdminFacade $orderListAdminFacade
    ) {
        $this->orderAdvancedSearchFormFactory = $orderAdvancedSearchFormFactory;
        $this->advancedSearchOrderService = $advancedSearchOrderService;
        $this->orderListAdminFacade = $orderListAdminFacade;
    }

    public function createAdvancedSearchOrderForm(Request $request): \Symfony\Component\Form\FormInterface
    {
        $rulesData = (array)$request->get(self::RULES_FORM_NAME);
        $rulesFormData = $this->advancedSearchOrderService->getRulesFormViewDataByRequestData($rulesData);

        return $this->orderAdvancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesFormData);
    }

    /**
     * @param string|int $index
     */
    public function createRuleForm(string $filterName, $index): \Symfony\Component\Form\FormInterface
    {
        $rulesData = [
            $index => $this->advancedSearchOrderService->createDefaultRuleFormViewData($filterName),
        ];

        return $this->orderAdvancedSearchFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesData);
    }
    
    public function getQueryBuilderByAdvancedSearchOrderData(array $advancedSearchOrderData): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
        $this->advancedSearchOrderService->extendQueryBuilderByAdvancedSearchOrderData($queryBuilder, $advancedSearchOrderData);

        return $queryBuilder;
    }

    public function isAdvancedSearchOrderFormSubmitted(Request $request): bool
    {
        $rulesData = $request->get(self::RULES_FORM_NAME);

        return $rulesData !== null;
    }
}
