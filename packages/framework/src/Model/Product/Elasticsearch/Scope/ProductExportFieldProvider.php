<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

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
    public const string BRAND_SLUG = 'brand_slug';
    public const string FLAGS = 'flags';
    public const string CATEGORIES = 'categories';
    public const string MAIN_CATEGORY_ID = 'main_category_id';
    public const string IN_STOCK = 'in_stock';
    public const string PRICES = 'prices';
    public const string SPECIAL_PRICES = 'special_prices';
    public const string PARAMETERS = 'parameters';
    public const string ORDERING_PRIORITY = 'ordering_priority';
    public const string SELLING_DENIED = 'selling_denied';
    public const string PERSONAL_PICKUP_ONLY = 'personal_pickup_only';
    public const string AVAILABILITY = 'availability';
    public const string IS_MAIN_VARIANT = 'is_main_variant';
    public const string IS_VARIANT = 'is_variant';
    public const string SLUG = 'slug';
    public const string VISIBILITY = 'visibility';
    public const string UUID = 'uuid';
    public const string UNIT = 'unit';
    public const string STOCK_QUANTITY = 'stock_quantity';
    public const string IS_ALLOWED_NEGATIVE_STOCK = 'is_allowed_negative_stock';
    public const string VARIANTS = 'variants';
    public const string MAIN_VARIANT_ID = 'main_variant_id';
    public const string SEO_H1 = 'seo_h1';
    public const string SEO_TITLE = 'seo_title';
    public const string SEO_META_DESCRIPTION = 'seo_meta_description';
    public const string ACCESSORIES = 'accessories';
    public const string RELATED_PRODUCTS = 'related_products';
    public const string HREFLANG_LINKS = 'hreflang_links';
    public const string PRODUCT_TYPE = 'product_type';
    public const string PRIORITY_BY_PRODUCT_TYPE = 'priority_by_product_type';
    public const string NAME_PREFIX = 'name_prefix';
    public const string NAME_SUFFIX = 'name_suffix';
    public const string AVAILABLE_STORES_COUNT = 'available_stores_count';
    public const string STORE_AVAILABILITIES_INFORMATION = 'store_availabilities_information';
    public const string AVAILABILITY_STATUS = 'availability_status';
    public const string VAT_PERCENT = 'vat_percent';

    public const string SELLING_FROM = 'selling_from';
    public const string PRODUCT_VIDEOS = 'product_videos';

    public const string PROMOTION = 'promotion';

    public const string IS_PROMOTED = 'is_promoted';
    public const string TOP_PRODUCT_POSITION = 'top_product_position';

    public const string SEARCHING_SEO_TITLES = 'searching_seo_titles';
    public const string SEARCHING_SEO_H1S = 'searching_seo_h1s';
    public const string SEARCHING_SEO_META_DESCRIPTIONS = 'searching_seo_meta_descriptions';

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface> $productExportDataProviders
     */
    public function __construct(
        #[AutowireIterator('shopsys.product_export_data_provider')]
        protected readonly iterable $productExportDataProviders = [],
    ) {
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        $exportFields = ReflectionHelper::getAllPublicClassConstants(static::class);

        foreach ($this->productExportDataProviders as $productExportDataProvider) {
            $exportFields = [...$exportFields, ...$productExportDataProvider->getExportFields()];
        }

        return array_values(array_unique($exportFields));
    }
}
