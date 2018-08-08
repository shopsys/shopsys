<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductListAdminFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminRepository
     */
    protected $productListAdminRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    public function __construct(
        ProductListAdminRepository $productListAdminRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        $this->productListAdminRepository = $productListAdminRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    public function getProductListQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        /**
         * temporary solution -
         * when product price type calculation is set to manual, price for first domain is shown in admin product list
         */
        $defaultPricingGroupId = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(1)->getId();

        return $this->productListAdminRepository->getProductListQueryBuilder($defaultPricingGroupId);
    }

    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getProductListQueryBuilder();
        $this->productListAdminRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
