<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductBrandFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCalculatedSellingDeniedFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCategoryFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductCatnumFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductFlagFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\ProductPartnoFilter;

class AdvancedSearchProductFilterTranslation extends AdvancedSearchFilterTranslation
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilterTranslation(ProductCatnumFilter::NAME, t('Catalog number'));
        $this->addFilterTranslation(ProductFlagFilter::NAME, t('Flag'));
        $this->addFilterTranslation(ProductNameFilter::NAME, t('Product name'));
        $this->addFilterTranslation(ProductPartnoFilter::NAME, t('PartNo (serial number)'));
        $this->addFilterTranslation(ProductCalculatedSellingDeniedFilter::NAME, t('Excluded from sale'));
        $this->addFilterTranslation(ProductBrandFilter::NAME, t('Brand'));
        $this->addFilterTranslation(ProductCategoryFilter::NAME, t('Category'));
    }
}
