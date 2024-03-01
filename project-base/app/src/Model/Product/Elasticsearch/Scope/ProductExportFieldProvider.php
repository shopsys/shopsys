<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider as BaseProductExportFieldProvider;

class ProductExportFieldProvider extends BaseProductExportFieldProvider
{
    public const string MAIN_CATEGORY_PATH = 'main_category_path';
    public const string IS_AVAILABLE = 'is_available';
    public const string AVAILABILITY_STATUS = 'availability_status';
    public const string NAME_PREFIX = 'name_prefix';
    public const string NAME_SUFIX = 'name_sufix';
    public const string IS_SALE_EXCLUSION = 'is_sale_exclusion';
    public const string PRODUCT_AVAILABLE_STORES_COUNT_INFORMATION = 'product_available_stores_count_information';
    public const string STORE_AVAILABILITIES_INFORMATION = 'store_availabilities_information';
    public const string USPS = 'usps';
    public const string SEARCHING_NAMES = 'searching_names';
    public const string SEARCHING_DESCRIPTIONS = 'searching_descriptions';
    public const string SEARCHING_CATNUMS = 'searching_catnums';
    public const string SEARCHING_EANS = 'searching_eans';
    public const string SEARCHING_PARTNOS = 'searching_partnos';
    public const string SEARCHING_SHORT_DESCRIPTIONS = 'searching_short_descriptions';
    public const string SLUG = 'slug';
    public const string AVAILABLE_STORES_COUNT = 'available_stores_count';
    public const string RELATED_PRODUCTS = 'related_products';
    public const string BREADCRUMB = 'breadcrumb';
    public const string PRODUCT_VIDEOS = 'product_videos';
}
