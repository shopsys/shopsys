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
        $result['product_count_exposed_in_stores'] = $product['product_count_exposed_in_stores'] ?? '';
        $result['store_availabilities_information'] = $product['store_availabilities_information'] ?? [];
        $result['has_preorder'] = $product['has_preorder'] ?? false;
        $result['slug'] = $product['slug'] ?? '';
        $result['available_stores_count'] = $product['available_stores_count'] ?? 0;
        $result['exposed_stores_count'] = $product['exposed_stores_count'] ?? 0;
        $result['related_products'] = $product['related_products'] ?? [];
        $result['product_videos'] = $product['product_videos'] ?? [];

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
            $filledParameter['parameter_is_dimensional'] = $parameter['parameter_is_dimensional'] ?? '';
            $filledParameter['parameter_group'] = $parameter['parameter_group'] ?? null;
            $filledParameter['parameter_unit'] = $parameter['parameter_unit'] ?? null;
            $filledParameter['parameter_value_for_slider_filter'] = $parameter['parameter_value_for_slider_filter'] ?? null;
            $results[] = $filledParameter;
        }

        return $results;
    }
}
