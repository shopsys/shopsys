<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ProductElasticsearchConverter
{
    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface> $productExportDataProviders
     */
    public function __construct(
        #[AutowireIterator('shopsys.product_export_data_provider')]
        protected readonly iterable $productExportDataProviders = [],
    ) {
    }

    public function fillEmptyFields(array $product): array
    {
        $result = $product;

        $result[ProductExportFieldProvider::ID] = $product[ProductExportFieldProvider::ID] ?? 0;
        $result[ProductExportFieldProvider::AVAILABILITY] = $product[ProductExportFieldProvider::AVAILABILITY] ?? '';
        $result[ProductExportFieldProvider::CATNUM] = $product[ProductExportFieldProvider::CATNUM] ?? '';
        $result[ProductExportFieldProvider::DESCRIPTION] = $product[ProductExportFieldProvider::DESCRIPTION] ?? '';
        $result[ProductExportFieldProvider::SLUG] = $product[ProductExportFieldProvider::SLUG] ?? '';
        $result[ProductExportFieldProvider::EAN] = $product[ProductExportFieldProvider::EAN] ?? '';
        $result[ProductExportFieldProvider::NAME] = $product[ProductExportFieldProvider::NAME] ?? '';
        $result[ProductExportFieldProvider::PARTNO] = $product[ProductExportFieldProvider::PARTNO] ?? '';
        $result[ProductExportFieldProvider::SHORT_DESCRIPTION] = $product[ProductExportFieldProvider::SHORT_DESCRIPTION] ?? '';

        $result[ProductExportFieldProvider::CATEGORIES] = $product[ProductExportFieldProvider::CATEGORIES] ?? [];
        $result[ProductExportFieldProvider::FLAGS] = $product[ProductExportFieldProvider::FLAGS] ?? [];
        $result[ProductExportFieldProvider::PARAMETERS] = array_key_exists(ProductExportFieldProvider::PARAMETERS, $product) && $product[ProductExportFieldProvider::PARAMETERS] ? $this->fillEmptyParameters($product[ProductExportFieldProvider::PARAMETERS]) : [];
        $result[ProductExportFieldProvider::PRICES] = array_key_exists(ProductExportFieldProvider::PRICES, $product) && $product[ProductExportFieldProvider::PRICES] ? $this->fillEmptyVariantPrices($product[ProductExportFieldProvider::PRICES]) : [];
        $result[ProductExportFieldProvider::SPECIAL_PRICES] = $product[ProductExportFieldProvider::SPECIAL_PRICES] ?? [];
        $result[ProductExportFieldProvider::VISIBILITY] = $product[ProductExportFieldProvider::VISIBILITY] ?? [];
        $result[ProductExportFieldProvider::ACCESSORIES] = $product[ProductExportFieldProvider::ACCESSORIES] ?? [];
        $result[ProductExportFieldProvider::RELATED_PRODUCTS] = $product[ProductExportFieldProvider::RELATED_PRODUCTS] ?? [];

        $result[ProductExportFieldProvider::ORDERING_PRIORITY] = $product[ProductExportFieldProvider::ORDERING_PRIORITY] ?? 0;

        $result[ProductExportFieldProvider::IN_STOCK] = $product[ProductExportFieldProvider::IN_STOCK] ?? false;
        $result[ProductExportFieldProvider::IS_MAIN_VARIANT] = $product[ProductExportFieldProvider::IS_MAIN_VARIANT] ?? false;
        $result[ProductExportFieldProvider::IS_VARIANT] = $product[ProductExportFieldProvider::IS_VARIANT] ?? false;
        $result[ProductExportFieldProvider::MAIN_VARIANT_ID] = $product[ProductExportFieldProvider::MAIN_VARIANT_ID] ?? null;
        $result[ProductExportFieldProvider::VARIANTS] = $product[ProductExportFieldProvider::VARIANTS] ?? [];

        $result[ProductExportFieldProvider::SELLING_DENIED] = $product[ProductExportFieldProvider::SELLING_DENIED] ?? true;
        $result[ProductExportFieldProvider::PERSONAL_PICKUP_ONLY] = $product[ProductExportFieldProvider::PERSONAL_PICKUP_ONLY] ?? false;

        // unknown default value, used for filtering only
        $result[ProductExportFieldProvider::BRAND] = $product[ProductExportFieldProvider::BRAND] ?? null;
        $result[ProductExportFieldProvider::BRAND_NAME] = $product[ProductExportFieldProvider::BRAND_NAME] ?? '';
        $result[ProductExportFieldProvider::BRAND_SLUG] = $product[ProductExportFieldProvider::BRAND_SLUG] ?? '';
        $result[ProductExportFieldProvider::MAIN_CATEGORY_ID] = $product[ProductExportFieldProvider::MAIN_CATEGORY_ID] ?? null;

        $result[ProductExportFieldProvider::SEO_H1] = $product[ProductExportFieldProvider::SEO_H1] ?? null;
        $result[ProductExportFieldProvider::SEO_TITLE] = $product[ProductExportFieldProvider::SEO_TITLE] ?? null;
        $result[ProductExportFieldProvider::SEO_META_DESCRIPTION] = $product[ProductExportFieldProvider::SEO_META_DESCRIPTION] ?? null;
        $result[ProductExportFieldProvider::HREFLANG_LINKS] = $product[ProductExportFieldProvider::HREFLANG_LINKS] ?? [];
        $result[ProductExportFieldProvider::PRODUCT_TYPE] = $product[ProductExportFieldProvider::PRODUCT_TYPE] ?? ProductTypeEnum::TYPE_BASIC;
        $result[ProductExportFieldProvider::PRIORITY_BY_PRODUCT_TYPE] = $product[ProductExportFieldProvider::PRIORITY_BY_PRODUCT_TYPE] ?? 0;

        $result[ProductExportFieldProvider::NAME_PREFIX] = $product[ProductExportFieldProvider::NAME_PREFIX] ?? null;
        $result[ProductExportFieldProvider::NAME_SUFFIX] = $product[ProductExportFieldProvider::NAME_SUFFIX] ?? $product['name_sufix'] ?? null;

        $result[ProductExportFieldProvider::AVAILABILITY_STATUS] = $product[ProductExportFieldProvider::AVAILABILITY_STATUS] ?? '';
        $result[ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION] = $product[ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION] ?? [];
        $result[ProductExportFieldProvider::AVAILABLE_STORES_COUNT] = $product[ProductExportFieldProvider::AVAILABLE_STORES_COUNT] ?? null;
        $result[ProductExportFieldProvider::STOCK_QUANTITY] = $product[ProductExportFieldProvider::STOCK_QUANTITY] ?? null;
        $result[ProductExportFieldProvider::IS_ALLOWED_NEGATIVE_STOCK] = $product[ProductExportFieldProvider::IS_ALLOWED_NEGATIVE_STOCK] ?? true;

        $result[ProductExportFieldProvider::UUID] = $product[ProductExportFieldProvider::UUID] ?? '00000000-0000-0000-0000-000000000000';
        $result[ProductExportFieldProvider::UNIT] = $product[ProductExportFieldProvider::UNIT] ?? '';

        $result[ProductExportFieldProvider::SELLING_FROM] = $product[ProductExportFieldProvider::SELLING_FROM] ?? null;
        $result[ProductExportFieldProvider::EXPECTED_RESTOCKING_DATE] = $product[ProductExportFieldProvider::EXPECTED_RESTOCKING_DATE] ?? null;

        $result[ProductExportFieldProvider::PRODUCT_VIDEOS] = $product[ProductExportFieldProvider::PRODUCT_VIDEOS] ?? [];

        $result[ProductExportFieldProvider::VAT_PERCENT] = $product[ProductExportFieldProvider::VAT_PERCENT] ?? '0';

        $result[ProductExportFieldProvider::PROMOTION] = $this->fillEmptyPromotion(
            $product[ProductExportFieldProvider::PROMOTION] ?? null,
        );

        $result[ProductExportFieldProvider::SEARCHING_SEO_TITLES] = $product[ProductExportFieldProvider::SEARCHING_SEO_TITLES] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_SEO_H1S] = $product[ProductExportFieldProvider::SEARCHING_SEO_H1S] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_SEO_META_DESCRIPTIONS] = $product[ProductExportFieldProvider::SEARCHING_SEO_META_DESCRIPTIONS] ?? '';

        $result[ProductExportFieldProvider::IS_PROMOTED] = $product[ProductExportFieldProvider::IS_PROMOTED] ?? false;
        $result[ProductExportFieldProvider::TOP_PRODUCT_POSITION] = $product[ProductExportFieldProvider::TOP_PRODUCT_POSITION] ?? null;

        foreach ($this->productExportDataProviders as $productExportDataProvider) {
            foreach ($productExportDataProvider->getExportFields() as $field) {
                $result[$field] = array_key_exists($field, $product) ? $product[$field] : $productExportDataProvider->getDefaultValue($field);
            }
        }

        return $result;
    }

    protected function fillEmptyParameters(array $parameters): array
    {
        $results = [];

        foreach ($parameters as $parameter) {
            $filledParameter = $parameter;
            $filledParameter['parameter_id'] = $parameter['parameter_id'] ?? '';
            $filledParameter['parameter_uuid'] = $parameter['parameter_uuid'] ?? '';
            $filledParameter['parameter_name'] = $parameter['parameter_name'] ?? '';
            $filledParameter['parameter_value_id'] = $parameter['parameter_value_id'] ?? '';
            $filledParameter['parameter_value_uuid'] = $parameter['parameter_value_uuid'] ?? '';
            $filledParameter['parameter_value_text'] = $parameter['parameter_value_text'] ?? '';
            $filledParameter['parameter_group'] = $parameter['parameter_group'] ?? null;
            $filledParameter['parameter_unit'] = $parameter['parameter_unit'] ?? null;
            $filledParameter['parameter_value_for_slider_filter'] = $parameter['parameter_value_for_slider_filter'] ?? null;
            $filledParameter['parameter_type'] = $parameter['parameter_type'] ?? null;
            $filledParameter['parameter_value_rgbHex'] = $parameter['parameter_value_rgbHex'] ?? null;
            $filledParameter['parameter_value_icon_anchor_text'] = $parameter['parameter_value_icon_anchor_text'] ?? null;
            $filledParameter['parameter_value_icon_url'] = $parameter['parameter_value_icon_url'] ?? null;
            $results[] = $filledParameter;
        }

        return $results;
    }

    protected function fillEmptyVariantPrices(array $prices): array
    {
        $results = [];

        foreach ($prices as $priceData) {
            $filledPriceData = $priceData;
            $filledPriceData['variant_prices'] = $priceData['variant_prices'] ?? [];
            $results[] = $filledPriceData;
        }

        return $results;
    }

    /**
     * @return array{buy_quantity: int|null, free_quantity: int|null}
     */
    protected function fillEmptyPromotion(?array $promotion): array
    {
        if (!is_array($promotion)) {
            return [
                'buy_quantity' => null,
                'free_quantity' => null,
            ];
        }

        return [
            'buy_quantity' => $promotion['buy_quantity'] ?? null,
            'free_quantity' => $promotion['free_quantity'] ?? null,
        ];
    }
}
