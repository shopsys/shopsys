<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductElasticsearchConverter
{
    /**
     * @param array $product
     * @return array
     */
    public function fillEmptyFields(array $product): array
    {
        $result = $product;

        $result['availability'] = $product['availability'] ?? '';
        $result['catnum'] = $product['catnum'] ?? '';
        $result['description'] = $product['description'] ?? '';
        $result['detail_url'] = $product['detail_url'] ?? '';
        $result['ean'] = $product['ean'] ?? '';
        $result['name'] = $product['name'] ?? '';
        $result['partno'] = $product['partno'] ?? '';
        $result['short_description'] = $product['short_description'] ?? '';

        $result['categories'] = $product['categories'] ?? [];
        $result['flags'] = $product['flags'] ?? [];
        $result['parameters'] = array_key_exists('parameters', $product) && $product['parameters'] ? $this->fillEmptyParameters($product['parameters']) : [];
        $result['prices'] = $product['prices'] ?? [];
        $result['visibility'] = $product['visibility'] ?? [];
        $result['accessories'] = $product['accessories'] ?? [];

        $result['ordering_priority'] = $product['ordering_priority'] ?? 0;

        $result['in_stock'] = $product['in_stock'] ?? false;
        $result['is_main_variant'] = $product['is_main_variant'] ?? false;
        $result['is_variant'] = $product['is_variant'] ?? false;
        $result['main_variant_id'] = $product['main_variant_id'] ?? null;
        $result['variants'] = $product['variants'] ?? [];

        $result['calculated_selling_denied'] = $product['calculated_selling_denied'] ?? true;
        $result['selling_denied'] = $product['selling_denied'] ?? true;

        // unknown default value, used for filtering only
        $result['brand'] = $product['brand'] ?? null;
        $result['brand_name'] = $product['brand_name'] ?? '';
        $result['brand_url'] = $product['brand_url'] ?? '';
        $result['main_category_id'] = $product['main_category_id'] ?? null;

        $result['seo_h1'] = $product['seo_h1'] ?? null;
        $result['seo_title'] = $product['seo_title'] ?? null;
        $result['seo_meta_description'] = $product['seo_meta_description'] ?? null;

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
            $filledParameter = [];
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
