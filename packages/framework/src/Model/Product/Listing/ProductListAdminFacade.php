<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductListAdminFacade
{
    public function __construct(
        protected readonly ProductListAdminRepository $productListAdminRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    public function getProductListQueryBuilder(): QueryBuilder
    {
        /**
         * temporary solution -
         * when product price type calculation is set to manual, price for first domain is shown in admin product list
         */
        $defaultPricingGroupId = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            Domain::FIRST_DOMAIN_ID,
        )->getId();

        return $this->productListAdminRepository->getProductListQueryBuilder($defaultPricingGroupId);
    }

    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        $queryBuilder = $this->getProductListQueryBuilder();
        $this->productListAdminRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
