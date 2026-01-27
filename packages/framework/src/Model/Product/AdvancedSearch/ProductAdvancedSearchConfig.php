<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductBrandFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCategoryFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCatnumFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductFlagFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductHasPromotionXyFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductPartnoFilter;
use Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductUponInquiryFilter;

class ProductAdvancedSearchConfig extends AdvancedSearchConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCatnumFilter $productCatnumFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductNameFilter $productNameFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductPartnoFilter $productPartnoFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductFlagFilter $productFlagFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter $productCalculatedSellingDeniedFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductBrandFilter $productBrandFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductCategoryFilter $productCategoryFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductUponInquiryFilter $productUponInquiryFilter
     * @param \Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\ProductHasPromotionXyFilter $productHasPromotionFilter
     */
    public function __construct(
        ProductCatnumFilter $productCatnumFilter,
        ProductNameFilter $productNameFilter,
        ProductPartnoFilter $productPartnoFilter,
        ProductFlagFilter $productFlagFilter,
        ProductCalculatedSellingDeniedFilter $productCalculatedSellingDeniedFilter,
        ProductBrandFilter $productBrandFilter,
        ProductCategoryFilter $productCategoryFilter,
        ProductUponInquiryFilter $productUponInquiryFilter,
        ProductHasPromotionXyFilter $productHasPromotionFilter,
    ) {
        parent::__construct();

        $this->registerFilter($productNameFilter);
        $this->registerFilter($productCatnumFilter);
        $this->registerFilter($productPartnoFilter);
        $this->registerFilter($productFlagFilter);
        $this->registerFilter($productCalculatedSellingDeniedFilter);
        $this->registerFilter($productBrandFilter);
        $this->registerFilter($productCategoryFilter);
        $this->registerFilter($productUponInquiryFilter);
        $this->registerFilter($productHasPromotionFilter);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface $filter
     */
    protected function unregisterFilter(AdvancedSearchFilterInterface $filter): void
    {
        $filterName = $filter->getName();

        if (array_key_exists($filterName, $this->filters) === false) {
            throw new AdvancedSearchFilterNotFoundException(sprintf('Filter "%s" is not registered.', $filterName));
        }

        unset($this->filters[$filterName]);
    }
}
