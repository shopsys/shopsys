<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductListAdminFacade
{
    public function __construct(
        protected readonly ProductListAdminRepository $productListAdminRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function getProductListQueryBuilder(): QueryBuilder
    {
        $pricingGroup = $this->pricingGroupSettingFacade->findDefaultPricingGroupByCurrency(
            $this->currencyFacade->getDefaultCurrency(),
        );

        return $this->productListAdminRepository->getProductListQueryBuilder($pricingGroup?->getId());
    }

    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        $queryBuilder = $this->getProductListQueryBuilder();
        $this->productListAdminRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
