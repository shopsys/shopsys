<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\ScopeRuleAlreadyExistsException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\ScopeRuleDoesNotExistException;

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
            ProductExportFieldProvider::CALCULATED_SELLING_DENIED,
        ], [
            self::PRECONDITION_SELLING_DENIED_RECALCULATION,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_UNIT, [
            ProductExportFieldProvider::UNIT,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_BRAND, [
            ProductExportFieldProvider::BRAND,
            ProductExportFieldProvider::BRAND_NAME,
            ProductExportFieldProvider::BRAND_URL,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_STOCKS, [
            ProductExportFieldProvider::AVAILABILITY,
            ProductExportFieldProvider::AVAILABILITY_DISPATCH_TIME,
            ProductExportFieldProvider::IN_STOCK,
            ProductExportFieldProvider::STOCK_QUANTITY,
        ]);
        $this->addNewExportScopeRule(self::SCOPE_URL, [
            ProductExportFieldProvider::DETAIL_URL,
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
            ProductExportFieldProvider::DETAIL_URL,
            ProductExportFieldProvider::HREFLANG_LINKS,
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
            ProductExportFieldProvider::CALCULATED_SELLING_DENIED,
            ProductExportFieldProvider::VISIBILITY,
            ProductExportFieldProvider::VARIANTS,
            ProductExportFieldProvider::IS_VARIANT,
            ProductExportFieldProvider::IS_MAIN_VARIANT,
            ProductExportFieldProvider::MAIN_VARIANT_ID,
        ], [
            self::PRECONDITION_VISIBILITY_RECALCULATION,
            self::PRECONDITION_SELLING_DENIED_RECALCULATION,
        ]);
    }

    /**
     * @param string $scopeName
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
     * @param string $scopeName
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

    /**
     * @param string $scopeName
     * @param array $exportFields
     * @param array $preconditions
     */
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
