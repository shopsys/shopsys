<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use App\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter as BaseProductElasticsearchConverter;

class ProductElasticsearchConverter extends BaseProductElasticsearchConverter
{
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
}
