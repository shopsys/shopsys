<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider as BaseProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig as BaseProductExportScopeConfig;

class ProductExportScopeConfig extends BaseProductExportScopeConfig
{
    public const string SCOPE_DESCRIPTION = 'product_description_scope';
    public const string SCOPE_SHORT_DESCRIPTION = 'product_short_description_scope';
    public const string SCOPE_CATNUM = 'product_catnum_scope';
    public const string SCOPE_EAN = 'product_ean_scope';
    public const string SCOPE_PARTNO = 'product_partno_scope';

    protected function loadProductExportScopeRules(): void
    {
        parent::loadProductExportScopeRules();

        $this->addExportFieldsToExistingScopeRule(self::SCOPE_CATEGORIES, [
            ProductExportFieldProvider::MAIN_CATEGORY_PATH,
            ProductExportFieldProvider::BREADCRUMB,
        ]);
        $this->addExportFieldsToExistingScopeRule(self::SCOPE_STOCKS, [
            ProductExportFieldProvider::AVAILABILITY_STATUS,
            ProductExportFieldProvider::PRODUCT_AVAILABLE_STORES_COUNT_INFORMATION,
            ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION,
            ProductExportFieldProvider::AVAILABLE_STORES_COUNT,
        ]);
        $this->addExportFieldsToExistingScopeRule(self::SCOPE_VARIANTS, [
            BaseProductExportFieldProvider::PARAMETERS,
            ProductExportFieldProvider::SEARCHING_NAMES,
            ProductExportFieldProvider::SEARCHING_DESCRIPTIONS,
            ProductExportFieldProvider::SEARCHING_CATNUMS,
            ProductExportFieldProvider::SEARCHING_EANS,
            ProductExportFieldProvider::SEARCHING_PARTNOS,
            ProductExportFieldProvider::SEARCHING_SHORT_DESCRIPTIONS,
        ]);
        $this->addExportFieldsToExistingScopeRule(self::SCOPE_SELLING_DENIED, [ProductExportFieldProvider::IS_SALE_EXCLUSION]);
        $this->addExportFieldsToExistingScopeRule(self::SCOPE_NAME, [
            ProductExportFieldProvider::SEARCHING_NAMES,
            ProductExportFieldProvider::SLUG,
            ProductExportFieldProvider::BREADCRUMB,
            ProductExportFieldProvider::NAME_PREFIX,
            ProductExportFieldProvider::NAME_SUFIX,
        ]);

        $this->addNewExportScopeRule(self::SCOPE_DESCRIPTION, [
            BaseProductExportFieldProvider::DESCRIPTION,
            ProductExportFieldProvider::SEARCHING_DESCRIPTIONS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_SHORT_DESCRIPTION, [
            BaseProductExportFieldProvider::SHORT_DESCRIPTION,
            ProductExportFieldProvider::SEARCHING_SHORT_DESCRIPTIONS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_CATNUM, [
            BaseProductExportFieldProvider::CATNUM,
            ProductExportFieldProvider::SEARCHING_CATNUMS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_EAN, [
            BaseProductExportFieldProvider::EAN,
            ProductExportFieldProvider::SEARCHING_EANS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_PARTNO, [
            BaseProductExportFieldProvider::PARTNO,
            ProductExportFieldProvider::SEARCHING_PARTNOS,
        ]);
    }
}
