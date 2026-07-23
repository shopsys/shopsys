<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\ScopeRuleAlreadyExistsException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\ScopeRuleDoesNotExistException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ProductExportScopeConfig
{
    public const array ALL_SCOPES = [];

    public const string SCOPE_NAME = 'product_name_scope';
    public const string SCOPE_UNIT = 'product_unit_scope';
    public const string SCOPE_BRAND = 'product_brand_scope';
    public const string SCOPE_STOCKS = 'product_stocks_scope';
    public const string SCOPE_FLAGS = 'product_flags_scope';
    public const string SCOPE_GIFT_FLAGS = 'product_gift_flags_scope';
    public const string SCOPE_PARAMETERS = 'product_parameters_scope';
    public const string SCOPE_URL = 'product_url_scope';
    public const string SCOPE_SELLING_DENIED = 'product_selling_denied_scope';
    public const string SCOPE_VARIANTS = 'product_variants_scope';
    public const string SCOPE_HIDDEN = 'product_hidden_scope';
    public const string SCOPE_SELLING_FROM = 'product_selling_from_scope';
    public const string SCOPE_SELLING_TO = 'product_selling_to_scope';
    public const string SCOPE_PRICE = 'product_price_scope';
    public const string SCOPE_CATEGORIES = 'product_categories_scope';

    public const string SCOPE_DOMAIN_URL = 'product_domain_url_scope';
    public const string SCOPE_TOP_PRODUCT = 'product_top_product_scope';

    public const string PRECONDITION_VISIBILITY_RECALCULATION = 'visibility_recalculation';
    public const string PRECONDITION_SELLING_DENIED_RECALCULATION = 'selling_denied_recalculation';
    public const string PRECONDITION_GIFT_FLAG_RECALCULATION = 'gift_flag_recalculation';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule[]|null $productExportScopeRules
     * @param iterable<\Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface> $productExportDataProviders
     */
    public function __construct(
        protected ?array $productExportScopeRules = null,
        #[AutowireIterator('shopsys.product_export_data_provider')]
        protected readonly iterable $productExportDataProviders = [],
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule[]
     */
    public function getProductExportScopeRules(): array
    {
        if ($this->productExportScopeRules === null) {
            $this->loadProductExportScopeRules();
        }

        return $this->productExportScopeRules;
    }

    /**
     * @return string[]
     */
    public function getAllProductExportScopes(): array
    {
        return array_keys($this->getProductExportScopeRules());
    }

    protected function loadProductExportScopeRules(): void
    {
        $this->productExportScopeRules = [];

        $this->addNewExportScopeRule(self::SCOPE_SELLING_DENIED, [
            ProductExportFieldProvider::SELLING_DENIED,
        ], [
            self::PRECONDITION_SELLING_DENIED_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_UNIT, [
            ProductExportFieldProvider::UNIT,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_BRAND, [
            ProductExportFieldProvider::BRAND,
            ProductExportFieldProvider::BRAND_NAME,
            ProductExportFieldProvider::BRAND_SLUG,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_STOCKS, [
            ProductExportFieldProvider::PRIORITY_BY_PRODUCT_TYPE,
            ProductExportFieldProvider::IN_STOCK,
            ProductExportFieldProvider::STOCK_QUANTITY,
            ProductExportFieldProvider::IS_ALLOWED_NEGATIVE_STOCK,
            ProductExportFieldProvider::AVAILABLE_STORES_COUNT,
            ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION,
            ProductExportFieldProvider::EXPECTED_RESTOCKING_DATE,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_URL, [
            ProductExportFieldProvider::SLUG,
            ProductExportFieldProvider::HREFLANG_LINKS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_FLAGS, [
            ProductExportFieldProvider::FLAGS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_PARAMETERS, [
            ProductExportFieldProvider::PARAMETERS,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_NAME, [
            ProductExportFieldProvider::NAME,
            ProductExportFieldProvider::SLUG,
            ProductExportFieldProvider::HREFLANG_LINKS,
            ProductExportFieldProvider::NAME_PREFIX,
            ProductExportFieldProvider::NAME_SUFFIX,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_HIDDEN, [
            ProductExportFieldProvider::VISIBILITY,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_SELLING_FROM, [
            ProductExportFieldProvider::VISIBILITY,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_SELLING_TO, [
            ProductExportFieldProvider::VISIBILITY,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_PRICE, [
            ProductExportFieldProvider::PRICES,
            ProductExportFieldProvider::SPECIAL_PRICES,
            ProductExportFieldProvider::VISIBILITY,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_CATEGORIES, [
            ProductExportFieldProvider::CATEGORIES,
            ProductExportFieldProvider::MAIN_CATEGORY_ID,
            ProductExportFieldProvider::VISIBILITY,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_VARIANTS, [
            ProductExportFieldProvider::VISIBILITY,
            ProductExportFieldProvider::VARIANTS,
            ProductExportFieldProvider::IS_VARIANT,
            ProductExportFieldProvider::IS_MAIN_VARIANT,
            ProductExportFieldProvider::MAIN_VARIANT_ID,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
            self::PRECONDITION_SELLING_DENIED_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_DOMAIN_URL, [
            ProductExportFieldProvider::DESCRIPTION,
            ProductExportFieldProvider::SEO_META_DESCRIPTION,
            ProductExportFieldProvider::SHORT_DESCRIPTION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_GIFT_FLAGS, [
            ProductExportFieldProvider::FLAGS,
        ], [
            self::PRECONDITION_GIFT_FLAG_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_TOP_PRODUCT, [
            ProductExportFieldProvider::IS_PROMOTED,
            ProductExportFieldProvider::TOP_PRODUCT_POSITION,
        ]);

        $this->addProductExportDataProviderFieldsToScopeRules();
    }

    protected function addProductExportDataProviderFieldsToScopeRules(): void
    {
        foreach ($this->productExportDataProviders as $productExportDataProvider) {
            foreach ($productExportDataProvider->getExportScopeRules() as $scopeName => $productExportScopeRule) {
                $this->addOrMergeExportScopeRule($scopeName, $productExportScopeRule);
            }
        }
    }

    protected function addOrMergeExportScopeRule(
        string $scopeName,
        ProductExportScopeRule $productExportScopeRule,
    ): void {
        if (!array_key_exists($scopeName, $this->productExportScopeRules)) {
            $this->productExportScopeRules[$scopeName] = $productExportScopeRule;

            return;
        }

        $scopeRule = $this->productExportScopeRules[$scopeName];
        $this->productExportScopeRules[$scopeName] = new ProductExportScopeRule(
            array_values(array_unique([...$scopeRule->productExportFields, ...$productExportScopeRule->productExportFields])),
            array_values(array_unique([...$scopeRule->productExportPreconditions, ...$productExportScopeRule->productExportPreconditions])),
        );
    }

    /**
     * @param string[] $exportFields
     * @param string[] $preconditions
     */
    protected function addNewExportScopeRule(string $scopeName, array $exportFields, array $preconditions = []): void
    {
        if (array_key_exists($scopeName, $this->productExportScopeRules)) {
            throw new ScopeRuleAlreadyExistsException($scopeName);
        }

        $this->productExportScopeRules[$scopeName] = new ProductExportScopeRule($exportFields, $preconditions);
    }

    /**
     * @param string[] $exportFields
     */
    protected function addExportFieldsToExistingScopeRule(string $scopeName, array $exportFields): void
    {
        if (!array_key_exists($scopeName, $this->productExportScopeRules)) {
            throw new ScopeRuleDoesNotExistException($scopeName);
        }
        $scopeRule = $this->productExportScopeRules[$scopeName];
        $this->productExportScopeRules[$scopeName] = new ProductExportScopeRule(
            [...$scopeRule->productExportFields, ...$exportFields],
            $scopeRule->productExportPreconditions,
        );
    }

    protected function overwriteExportScopeRule(
        string $scopeName,
        array $exportFields,
        array $preconditions = [],
    ): void {
        if (!array_key_exists($scopeName, $this->productExportScopeRules)) {
            throw new ScopeRuleDoesNotExistException($scopeName);
        }

        $this->productExportScopeRules[$scopeName] = new ProductExportScopeRule($exportFields, $preconditions);
    }
}
