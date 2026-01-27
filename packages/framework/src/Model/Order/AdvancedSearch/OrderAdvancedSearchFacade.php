<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPriceFilterWithVatFilter;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade;

class OrderAdvancedSearchFacade extends AbstractAdvancedSearchFacade
{
    public function __construct(
        OrderAdvancedSearchFormFactory $orderAdvancedSearchFormFactory,
        RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly OrderListAdminFacade $orderListAdminFacade,
    ) {
        parent::__construct($orderAdvancedSearchFormFactory, $ruleFormViewDataFactory);
    }

    public function getQueryBuilderByAdvancedSearchOrderData(
        array $advancedSearchOrderData,
    ): QueryBuilder {
        $queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchOrderData);

        return $queryBuilder;
    }

    /**
     * @return string
     */
    #[Override]
    protected function getDefaultFilterName(): string
    {
        return OrderPriceFilterWithVatFilter::NAME;
    }
}
