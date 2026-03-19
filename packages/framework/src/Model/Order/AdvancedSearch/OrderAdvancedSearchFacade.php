<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\OrderPriceFilterWithVatFilter;
use Shopsys\FrameworkBundle\Model\Order\Listing\OrderListAdminFacade;

class OrderAdvancedSearchFacade extends AbstractAdvancedSearchFacade
{
    public function __construct(
        AdvancedSearchFormFactory $advancedSearchFormFactory,
        RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly OrderListAdminFacade $orderListAdminFacade,
    ) {
        parent::__construct($advancedSearchFormFactory, $ruleFormViewDataFactory);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData|null> $advancedSearchOrderData
     */
    public function getQueryBuilderByAdvancedSearchOrderData(
        array $advancedSearchOrderData,
    ): QueryBuilder {
        $queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchOrderData, static::getEntityType());

        return $queryBuilder;
    }

    #[Override]
    protected function getDefaultFilterName(): string
    {
        return OrderPriceFilterWithVatFilter::NAME;
    }

    #[Override]
    public static function getEntityType(): string
    {
        return 'order';
    }

    #[Override]
    public function getRuleFormTemplatePath(): string
    {
        return '@ShopsysAdministration/content/order/advancedSearch/ruleForm.html.twig';
    }
}
