<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

class ProductExportScopeConfigFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig $productExportScopeConfig
     */
    public function __construct(
        protected readonly ProductExportScopeConfig $productExportScopeConfig,
    ) {
    }

    /**
     * @param string[] $exportScopes
     * @return string[]
     */
    public function getExportFieldsByScopes(array $exportScopes): array
    {
        $fields = [];

        foreach ($this->getMatchingRules($exportScopes) as $rule) {
            $fields = [...$fields, ...$rule->productExportFields];
        }

        return $fields;
    }

    /**
     * @param string[] $exportScopes
     * @return bool
     */
    public function shouldRecalculateVisibility(array $exportScopes): bool
    {
        foreach ($this->getMatchingRules($exportScopes) as $rule) {
            if (in_array(ProductExportScopeConfig::PRECONDITION_VISIBILITY_RECALCULATION, $rule->productExportPreconditions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $exportScopes
     * @return bool
     */
    public function shouldRecalculateSellingDenied(array $exportScopes): bool
    {
        foreach ($this->getMatchingRules($exportScopes) as $rule) {
            if (in_array(ProductExportScopeConfig::PRECONDITION_SELLING_DENIED_RECALCULATION, $rule->productExportPreconditions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $exportScopes
     * @return \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule[]
     */
    protected function getMatchingRules(array $exportScopes): array
    {
        $allRules = $this->productExportScopeConfig->getProductExportScopeRules();

        if (count($exportScopes) === 0) {
            return $allRules;
        }

        $matchingRules = [];

        foreach ($allRules as $productExportScope => $productExportRule) {
            if (in_array($productExportScope, $exportScopes, true)) {
                $matchingRules[] = $productExportRule;
            }
        }

        return $matchingRules;
    }
}
