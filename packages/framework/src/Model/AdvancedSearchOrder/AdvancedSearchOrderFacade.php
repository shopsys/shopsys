<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter\OrderPriceFilterWithVatFilter;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchOrderFacade
{
    public const RULES_FORM_NAME = 'as';

    public function __construct(
        protected readonly OrderAdvancedSearchFormFactory $orderAdvancedSearchFormFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly OrderListAdminFacade $orderListAdminFacade,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAdvancedSearchOrderForm(Request $request)
    {
        $rulesData = $request->query->all(static::RULES_FORM_NAME);
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            OrderPriceFilterWithVatFilter::NAME,
            $rulesData,
        );

        return $this->orderAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData);
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

        return $this->orderAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    /**
     * @param array $advancedSearchOrderData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByAdvancedSearchOrderData($advancedSearchOrderData)
    {
        $queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchOrderData);

        return $queryBuilder;
    }

    /**
     * @return bool
     */
    public function isAdvancedSearchOrderFormSubmitted(Request $request)
    {
        return $request->query->has(static::RULES_FORM_NAME);
    }
}
