<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export;

use Shopsys\FrameworkBundle\Component\Elasticsearch\ExportFieldEnumInterface;

enum ProductExportFieldEnum: string implements ExportFieldEnumInterface
{
    case ID = 'id';
    case CATNUM = 'catnum';
    case PARTNO = 'partno';
    case EAN = 'ean';
    case NAME = 'name';
    case DESCRIPTION = 'description';
    case SHORT_DESCRIPTION = 'short_description';
    case BRAND = 'brand';
    case BRAND_NAME = 'brand_name';
    case BRAND_URL = 'brand_url';
    case FLAGS = 'flags';
    case CATEGORIES = 'categories';
    case MAIN_CATEGORY_ID = 'main_category_id';
    case IN_STOCK = 'in_stock';
    case PRICES = 'prices';
    case PARAMETERS = 'parameters';
    case ORDERING_PRIORITY = 'ordering_priority';
    case CALCULATED_SELLING_DENIED = 'calculated_selling_denied';
    case SELLING_DENIED = 'selling_denied';
    case AVAILABILITY = 'availability';
    case AVAILABILITY_DISPATCH_TIME = 'availability_dispatch_time';
    case IS_MAIN_VARIANT = 'is_main_variant';
    case IS_VARIANT = 'is_variant';
    case DETAIL_URL = 'detail_url';
    case VISIBILITY = 'visibility';
    case UUID = 'uuid';
    case UNIT = 'unit';
    case STOCK_QUANTITY = 'stock_quantity';
    case VARIANTS = 'variants';
    case MAIN_VARIANT_ID = 'main_variant_id';
    case SEO_H1 = 'seo_h1';
    case SEO_TITLE = 'seo_title';
    case SEO_META_DESCRIPTION = 'seo_meta_description';
    case ACCESSORIES = 'accessories';
    case HREFLANG_LINKS = 'hreflang_links';
}
