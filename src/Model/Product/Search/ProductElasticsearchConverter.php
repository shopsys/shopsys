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
        $result['non_selling_price'] = $product['non_selling_price'] ?? null;
        $result['is_in_sale'] = $product['is_in_sale'] ?? false;

        return $result;
    }
}
