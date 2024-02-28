<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

class ProductExportScopeConfig
{
    public const string SCOPE_NAME = 'product_name_scope';
    public const string SCOPE_UNIT = 'product_unit_scope';
    public const string SCOPE_BRAND = 'product_brand_scope';
    public const string SCOPE_STOCKS = 'product_stocks_scope';
    public const string SCOPE_FLAGS = 'product_flags_scope';
    public const string SCOPE_PARAMETERS = 'product_parameters_scope';
    public const string SCOPE_URL = 'product_url_scope';
    public const string SCOPE_SELLING_DENIED = 'product_selling_denied_scope';
    public const string SCOPE_VARIANTS = 'product_variants_scope';
    public const string SCOPE_HIDDEN = 'product_hidden_scope';
    public const string SCOPE_SELLING_FROM = 'product_selling_from_scope';
    public const string SCOPE_SELLING_TO = 'product_selling_to_scope';
    public const string SCOPE_PRICE = 'product_price_scope';
    public const string SCOPE_CATEGORIES = 'product_categories_scope';

    public const string PRECONDITION_VISIBILITY_RECALCULATION = 'visibility_recalculation';
    public const string PRECONDITION_SELLING_DENIED_RECALCULATION = 'selling_denied_recalculation';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule[]|null $productExportScopeRules
     */
    public function __construct(
        protected ?array $productExportScopeRules = null,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule[]
     */
    public function getProductExportScopeRules(): array
    {
        if ($this->productExportScopeRules === null) {
            $this->productExportScopeRules = [
                self::SCOPE_SELLING_DENIED => new ProductExportScopeRule([
                    ProductExportFieldProvider::SELLING_DENIED,
                    ProductExportFieldProvider::CALCULATED_SELLING_DENIED,
                ], [
                    self::PRECONDITION_SELLING_DENIED_RECALCULATION,
                ]),
                self::SCOPE_UNIT => new ProductExportScopeRule([
                    ProductExportFieldProvider::UNIT,
                ]),
                self::SCOPE_BRAND => new ProductExportScopeRule([
                    ProductExportFieldProvider::BRAND,
                    ProductExportFieldProvider::BRAND_NAME,
                    ProductExportFieldProvider::BRAND_URL,
                ]),
                self::SCOPE_STOCKS => new ProductExportScopeRule([
                    ProductExportFieldProvider::AVAILABILITY,
                    ProductExportFieldProvider::AVAILABILITY_DISPATCH_TIME,
                    ProductExportFieldProvider::IN_STOCK,
                    ProductExportFieldProvider::STOCK_QUANTITY,
                ]),
                self::SCOPE_URL => new ProductExportScopeRule([
                    ProductExportFieldProvider::DETAIL_URL,
                    ProductExportFieldProvider::HREFLANG_LINKS,
                ]),
                self::SCOPE_FLAGS => new ProductExportScopeRule([
                    ProductExportFieldProvider::FLAGS,
                ]),
                self::SCOPE_PARAMETERS => new ProductExportScopeRule([
                    ProductExportFieldProvider::PARAMETERS,
                ]),
                self::SCOPE_NAME => new ProductExportScopeRule([
                    ProductExportFieldProvider::NAME,
                    ProductExportFieldProvider::DETAIL_URL,
                    ProductExportFieldProvider::HREFLANG_LINKS,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_HIDDEN => new ProductExportScopeRule([
                    ProductExportFieldProvider::VISIBILITY,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_SELLING_FROM => new ProductExportScopeRule([
                    ProductExportFieldProvider::VISIBILITY,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_SELLING_TO => new ProductExportScopeRule([
                    ProductExportFieldProvider::VISIBILITY,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_PRICE => new ProductExportScopeRule([
                    ProductExportFieldProvider::PRICES,
                    ProductExportFieldProvider::VISIBILITY,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_CATEGORIES => new ProductExportScopeRule([
                    ProductExportFieldProvider::CATEGORIES,
                    ProductExportFieldProvider::MAIN_CATEGORY_ID,
                    ProductExportFieldProvider::VISIBILITY,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                ]),
                self::SCOPE_VARIANTS => new ProductExportScopeRule([
                    ProductExportFieldProvider::CALCULATED_SELLING_DENIED,
                    ProductExportFieldProvider::VISIBILITY,
                    ProductExportFieldProvider::VARIANTS,
                    ProductExportFieldProvider::IS_VARIANT,
                    ProductExportFieldProvider::IS_MAIN_VARIANT,
                    ProductExportFieldProvider::MAIN_VARIANT_ID,
                ], [
                    self::PRECONDITION_VISIBILITY_RECALCULATION,
                    self::PRECONDITION_SELLING_DENIED_RECALCULATION,
                ]),
            ];
        }

        return $this->productExportScopeRules;
    }
}
