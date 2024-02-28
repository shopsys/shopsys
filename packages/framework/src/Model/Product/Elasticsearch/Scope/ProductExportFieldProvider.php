<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

class ProductExportFieldProvider
{
    public const string ID = 'id';
    public const string CATNUM = 'catnum';
    public const string PARTNO = 'partno';
    public const string EAN = 'ean';
    public const string NAME = 'name';
    public const string DESCRIPTION = 'description';
    public const string SHORT_DESCRIPTION = 'short_description';
    public const string BRAND = 'brand';
    public const string BRAND_NAME = 'brand_name';
    public const string BRAND_URL = 'brand_url';
    public const string FLAGS = 'flags';
    public const string CATEGORIES = 'categories';
    public const string MAIN_CATEGORY_ID = 'main_category_id';
    public const string IN_STOCK = 'in_stock';
    public const string PRICES = 'prices';
    public const string PARAMETERS = 'parameters';
    public const string ORDERING_PRIORITY = 'ordering_priority';
    public const string CALCULATED_SELLING_DENIED = 'calculated_selling_denied';
    public const string SELLING_DENIED = 'selling_denied';
    public const string AVAILABILITY = 'availability';
    public const string AVAILABILITY_DISPATCH_TIME = 'availability_dispatch_time';
    public const string IS_MAIN_VARIANT = 'is_main_variant';
    public const string IS_VARIANT = 'is_variant';
    public const string DETAIL_URL = 'detail_url';
    public const string VISIBILITY = 'visibility';
    public const string UUID = 'uuid';
    public const string UNIT = 'unit';
    public const string STOCK_QUANTITY = 'stock_quantity';
    public const string VARIANTS = 'variants';
    public const string MAIN_VARIANT_ID = 'main_variant_id';
    public const string SEO_H1 = 'seo_h1';
    public const string SEO_TITLE = 'seo_title';
    public const string SEO_META_DESCRIPTION = 'seo_meta_description';
    public const string ACCESSORIES = 'accessories';
    public const string HREFLANG_LINKS = 'hreflang_links';

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return ReflectionHelper::getAllPublicClassConstants(static::class);
    }
}
