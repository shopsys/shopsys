<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter\OrderPriceFilterWithVatFilter;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade;
use Symfony\Component\Form\FormInterface;
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

    public function createAdvancedSearchOrderForm(Request $request): FormInterface
    {
        $rulesData = $request->query->all(static::RULES_FORM_NAME);
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            OrderPriceFilterWithVatFilter::NAME,
            $rulesData,
        );

        return $this->orderAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData);
    }

    public function createRuleForm(string $filterName, string|int $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->orderAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    public function getQueryBuilderByAdvancedSearchOrderData(
        array $advancedSearchOrderData,
    ): QueryBuilder {
        $queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchOrderData);

        return $queryBuilder;
    }

    public function isAdvancedSearchOrderFormSubmitted(Request $request): bool
    {
        return $request->query->has(static::RULES_FORM_NAME);
    }
}
