<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductBrandFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCategoryFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCatnumFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductFlagFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductHasPromotionXyFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductPartnoFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductUponInquiryFilter;

class ProductAdvancedSearchConfig extends AdvancedSearchConfig
{
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

    protected function unregisterFilter(AdvancedSearchFilterInterface $filter): void
    {
        $filterName = $filter->getName();

        if (array_key_exists($filterName, $this->filters) === false) {
            throw new AdvancedSearchFilterNotFoundException(sprintf('Filter "%s" is not registered.', $filterName));
        }

        unset($this->filters[$filterName]);
    }
}
