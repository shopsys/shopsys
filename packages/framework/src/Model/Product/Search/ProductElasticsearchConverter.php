<?php

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
        $result['parameters'] = $product['parameters'] ?? [];
        $result['prices'] = $product['prices'] ?? [];
        $result['visibility'] = $product['visibility'] ?? [];

        $result['ordering_priority'] = $product['ordering_priority'] ?? 0;

        $result['in_stock'] = $product['in_stock'] ?? false;
        $result['is_main_variant'] = $product['is_main_variant'] ?? false;
        $result['main_variant_id'] = $product['main_variant_id'] ?? null;

        $result['calculated_selling_denied'] = $product['calculated_selling_denied'] ?? true;
        $result['selling_denied'] = $product['selling_denied'] ?? true;

        // unknown default value, used for filtering only
        $result['brand'] = $product['brand'] ?? null;

        return $result;
    }
}
