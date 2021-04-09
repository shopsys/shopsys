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
        $result['name_prefix'] = $product['name_prefix'] ?? '';
        $result['name_sufix'] = $product['name_sufix'] ?? '';
        $result['non_selling_price_with_vat'] = $product['non_selling_price_with_vat'] ?? null;
        $result['is_in_sale'] = $product['is_in_sale'] ?? false;
        $result['usps'] = $product['usps'] ?? [];
        $result['availability_status'] = $product['availability_status'] ?? '';
        $result['product_available_stocks_count_information'] = $product['product_available_stocks_count_information'] ?? '';
        $result['product_count_exposed_in_stores'] = $product['product_count_exposed_in_stores'] ?? '';
        $result['stock_availabilities_information'] = $product['stock_availabilities_information'] ?? [];
        $result['has_preorder'] = $product['has_preorder'] ?? false;

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
            $results[] = $filledParameter;
        }

        return $results;
    }
}
