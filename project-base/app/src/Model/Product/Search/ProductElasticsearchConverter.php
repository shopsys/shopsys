<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter as BaseProductElasticsearchConverter;

class ProductElasticsearchConverter extends BaseProductElasticsearchConverter
{
    /**
     * @param array $product
     * @return array
     */
    public function fillEmptyFields(array $product): array
    {
        $result = parent::fillEmptyFields($product);
        $result['name_prefix'] = $product['name_prefix'] ?? null;
        $result['name_sufix'] = $product['name_sufix'] ?? null;
        $result['usps'] = $product['usps'] ?? [];
        $result['availability_status'] = $product['availability_status'] ?? '';
        $result['product_available_stores_count_information'] = $product['product_available_stores_count_information'] ?? '';
        $result['store_availabilities_information'] = $product['store_availabilities_information'] ?? [];
        $result['has_preorder'] = $product['has_preorder'] ?? false;
        $result['slug'] = $product['slug'] ?? '';
        $result['available_stores_count'] = $product['available_stores_count'] ?? 0;
        $result['related_products'] = $product['related_products'] ?? [];
        $result['product_videos'] = $product['product_videos'] ?? [];
        $result['searching_names'] = $product['searching_names'] ?? '';
        $result['searching_catnums'] = $product['searching_catnums'] ?? '';
        $result['searching_partnos'] = $product['searching_partnos'] ?? '';
        $result['searching_eans'] = $product['searching_eans'] ?? '';
        $result['searching_short_descriptions'] = $product['searching_short_descriptions'] ?? '';
        $result['searching_descriptions'] = $product['searching_descriptions'] ?? '';
        $result['is_available'] = $product['is_available'] ?? false;
        $result['availability_dispatch_time'] = $product['availability_dispatch_time'] ?? null;
        $result['uuid'] = $product['uuid'] ?? '00000000-0000-0000-0000-000000000000';
        $result['unit'] = $product['unit'] ?? '';
        $result['stock_quantity'] = $product['stock_quantity'] ?? 0;
        $result['is_sale_exclusion'] = $product['is_sale_exclusion'] ?? true;
        $result['files'] = $product['files'] ?? [];
        $result['main_category_path'] = $product['main_category_path'] ?? '';
        $result['breadcrumb'] = $product['breadcrumb'] ?? [];

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
            $filledParameter['parameter_is_dimensional'] = $parameter['parameter_is_dimensional'] ?? '';
            $filledParameter['parameter_group'] = $parameter['parameter_group'] ?? null;
            $filledParameter['parameter_unit'] = $parameter['parameter_unit'] ?? null;
            $filledParameter['parameter_value_for_slider_filter'] = $parameter['parameter_value_for_slider_filter'] ?? null;
            $results[] = $filledParameter;
        }

        return $results;
    }
}
