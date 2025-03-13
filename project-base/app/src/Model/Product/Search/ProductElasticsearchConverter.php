<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use App\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter as BaseProductElasticsearchConverter;

class ProductElasticsearchConverter extends BaseProductElasticsearchConverter
{
    /**
     * @param array $product
     * @return array
     */
    #[Override]
    public function fillEmptyFields(array $product): array
    {
        $result = parent::fillEmptyFields($product);
        $result[ProductExportFieldProvider::USPS] = $product[ProductExportFieldProvider::USPS] ?? [];
        $result[ProductExportFieldProvider::RELATED_PRODUCTS] = $product[ProductExportFieldProvider::RELATED_PRODUCTS] ?? [];
        $result[ProductExportFieldProvider::SEARCHING_NAMES] = $product[ProductExportFieldProvider::SEARCHING_NAMES] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_CATNUMS] = $product[ProductExportFieldProvider::SEARCHING_CATNUMS] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_PARTNOS] = $product[ProductExportFieldProvider::SEARCHING_PARTNOS] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_EANS] = $product[ProductExportFieldProvider::SEARCHING_EANS] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_SHORT_DESCRIPTIONS] = $product[ProductExportFieldProvider::SEARCHING_SHORT_DESCRIPTIONS] ?? '';
        $result[ProductExportFieldProvider::SEARCHING_DESCRIPTIONS] = $product[ProductExportFieldProvider::SEARCHING_DESCRIPTIONS] ?? '';

        $result[ProductExportFieldProvider::MAIN_CATEGORY_PATH] = $product[ProductExportFieldProvider::MAIN_CATEGORY_PATH] ?? '';
        $result[ProductExportFieldProvider::BREADCRUMB] = $product[ProductExportFieldProvider::BREADCRUMB] ?? [];

        return $result;
    }

    /**
     * @param array $parameters
     * @return array
     */
    #[Override]
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
            $filledParameter['ordering_priority'] = $parameter['ordering_priority'] ?? null;
            $filledParameter['parameter_type'] = $parameter['parameter_type'] ?? null;
            $results[] = $filledParameter;
        }

        return $results;
    }
}
