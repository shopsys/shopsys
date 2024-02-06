<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterNotFoundException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductBrandFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCategoryFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCatnumFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductFlagFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductPartnoFilter;

class ProductAdvancedSearchConfig extends AdvancedSearchConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCatnumFilter $productCatnumFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter $productNameFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductPartnoFilter $productPartnoFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductFlagFilter $productFlagFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter $productCalculatedSellingDeniedFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductBrandFilter $productBrandFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCategoryFilter $productCategoryFilter
     */
    public function __construct(
        ProductCatnumFilter $productCatnumFilter,
        ProductNameFilter $productNameFilter,
        ProductPartnoFilter $productPartnoFilter,
        ProductFlagFilter $productFlagFilter,
        ProductCalculatedSellingDeniedFilter $productCalculatedSellingDeniedFilter,
        ProductBrandFilter $productBrandFilter,
        ProductCategoryFilter $productCategoryFilter,
    ) {
        parent::__construct();

        $this->registerFilter($productNameFilter);
        $this->registerFilter($productCatnumFilter);
        $this->registerFilter($productPartnoFilter);
        $this->registerFilter($productFlagFilter);
        $this->registerFilter($productCalculatedSellingDeniedFilter);
        $this->registerFilter($productBrandFilter);
        $this->registerFilter($productCategoryFilter);
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
