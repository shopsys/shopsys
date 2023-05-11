<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductListAdminFacade
{
    protected ProductListAdminRepository $productListAdminRepository;

    protected PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminRepository $productListAdminRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        ProductListAdminRepository $productListAdminRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        $this->productListAdminRepository = $productListAdminRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductListQueryBuilder()
    {
        /**
         * temporary solution -
         * when product price type calculation is set to manual, price for first domain is shown in admin product list
         */
        $defaultPricingGroupId = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            Domain::FIRST_DOMAIN_ID
        )->getId();

        return $this->productListAdminRepository->getProductListQueryBuilder($defaultPricingGroupId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData)
    {
        $queryBuilder = $this->getProductListQueryBuilder();
        $this->productListAdminRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
