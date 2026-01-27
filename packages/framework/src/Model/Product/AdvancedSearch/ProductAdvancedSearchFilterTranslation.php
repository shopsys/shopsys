<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;

class ProductAdvancedSearchFilterTranslation extends AdvancedSearchFilterTranslation
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilterTranslation(Filter\ProductCatnumFilter::NAME, t('Catalog number'));
        $this->addFilterTranslation(Filter\ProductFlagFilter::NAME, t('Flag'));
        $this->addFilterTranslation(Filter\ProductHasPromotionXyFilter::NAME, t('Has X+Y promotion'));
        $this->addFilterTranslation(Filter\ProductNameFilter::NAME, t('Product name'));
        $this->addFilterTranslation(Filter\ProductPartnoFilter::NAME, t('PartNo (serial number)'));
        $this->addFilterTranslation(Filter\ProductCalculatedSellingDeniedFilter::NAME, t('Excluded from sale'));
        $this->addFilterTranslation(Filter\ProductBrandFilter::NAME, t('Brand'));
        $this->addFilterTranslation(Filter\ProductCategoryFilter::NAME, t('Category'));
        $this->addFilterTranslation(Filter\ProductUponInquiryFilter::NAME, t('Upon inquiry'));
    }
}
