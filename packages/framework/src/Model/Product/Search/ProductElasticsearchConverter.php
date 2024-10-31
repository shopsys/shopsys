<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;

class ProductElasticsearchConverter
{
    /**
     * @param array $product
     * @return array
     */
    public function fillEmptyFields(array $product): array
    {
        $result = $product;

        $result[ProductExportFieldProvider::ID] = $product[ProductExportFieldProvider::ID] ?? 0;
        $result[ProductExportFieldProvider::AVAILABILITY] = $product[ProductExportFieldProvider::AVAILABILITY] ?? '';
        $result[ProductExportFieldProvider::CATNUM] = $product[ProductExportFieldProvider::CATNUM] ?? '';
        $result[ProductExportFieldProvider::DESCRIPTION] = $product[ProductExportFieldProvider::DESCRIPTION] ?? '';
        $result[ProductExportFieldProvider::DETAIL_URL] = $product[ProductExportFieldProvider::DETAIL_URL] ?? '';
        $result[ProductExportFieldProvider::EAN] = $product[ProductExportFieldProvider::EAN] ?? '';
        $result[ProductExportFieldProvider::NAME] = $product[ProductExportFieldProvider::NAME] ?? '';
        $result[ProductExportFieldProvider::PARTNO] = $product[ProductExportFieldProvider::PARTNO] ?? '';
        $result[ProductExportFieldProvider::SHORT_DESCRIPTION] = $product[ProductExportFieldProvider::SHORT_DESCRIPTION] ?? '';

        $result[ProductExportFieldProvider::CATEGORIES] = $product[ProductExportFieldProvider::CATEGORIES] ?? [];
        $result[ProductExportFieldProvider::FLAGS] = $product[ProductExportFieldProvider::FLAGS] ?? [];
        $result[ProductExportFieldProvider::PARAMETERS] = array_key_exists(ProductExportFieldProvider::PARAMETERS, $product) && $product[ProductExportFieldProvider::PARAMETERS] ? $this->fillEmptyParameters($product[ProductExportFieldProvider::PARAMETERS]) : [];
        $result[ProductExportFieldProvider::PRICES] = $product[ProductExportFieldProvider::PRICES] ?? [];
        $result[ProductExportFieldProvider::VISIBILITY] = $product[ProductExportFieldProvider::VISIBILITY] ?? [];
        $result[ProductExportFieldProvider::ACCESSORIES] = $product[ProductExportFieldProvider::ACCESSORIES] ?? [];

        $result[ProductExportFieldProvider::ORDERING_PRIORITY] = $product[ProductExportFieldProvider::ORDERING_PRIORITY] ?? 0;

        $result[ProductExportFieldProvider::IN_STOCK] = $product[ProductExportFieldProvider::IN_STOCK] ?? false;
        $result[ProductExportFieldProvider::IS_MAIN_VARIANT] = $product[ProductExportFieldProvider::IS_MAIN_VARIANT] ?? false;
        $result[ProductExportFieldProvider::IS_VARIANT] = $product[ProductExportFieldProvider::IS_VARIANT] ?? false;
        $result[ProductExportFieldProvider::MAIN_VARIANT_ID] = $product[ProductExportFieldProvider::MAIN_VARIANT_ID] ?? null;
        $result[ProductExportFieldProvider::VARIANTS] = $product[ProductExportFieldProvider::VARIANTS] ?? [];

        $result[ProductExportFieldProvider::CALCULATED_SELLING_DENIED] = $product[ProductExportFieldProvider::CALCULATED_SELLING_DENIED] ?? true;
        $result[ProductExportFieldProvider::SELLING_DENIED] = $product[ProductExportFieldProvider::SELLING_DENIED] ?? true;

        // unknown default value, used for filtering only
        $result[ProductExportFieldProvider::BRAND] = $product[ProductExportFieldProvider::BRAND] ?? null;
        $result[ProductExportFieldProvider::BRAND_NAME] = $product[ProductExportFieldProvider::BRAND_NAME] ?? '';
        $result[ProductExportFieldProvider::BRAND_URL] = $product[ProductExportFieldProvider::BRAND_URL] ?? '';
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

        $result[ProductExportFieldProvider::UUID] = $product[ProductExportFieldProvider::UUID] ?? '00000000-0000-0000-0000-000000000000';
        $result[ProductExportFieldProvider::UNIT] = $product[ProductExportFieldProvider::UNIT] ?? '';

        $result['vat'] = $product['vat'] ?? ['percent' => '0'];

        return $result;
    }

    /**
     * @param array $parameters
     * @return array
     */
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
            $results[] = $filledParameter;
        }

        return $results;
    }
}
