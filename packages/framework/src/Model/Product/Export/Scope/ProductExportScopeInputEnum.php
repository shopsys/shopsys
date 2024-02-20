<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface;

// TODO je problém s rozšiřitelností, nemám jak udělat ExportScopeInputCasesProvider, protože enum nejde instanciovat, nemůže být jako servisa v kontejneru
// TODO budu muset ustoupit od používání enumu a uděla to jako třídu s konstantami :P
enum ProductExportScopeInputEnum: string implements ExportScopeInputEnumInterface
{
    case PRODUCT_NAME = 'product_name';
    case PRODUCT_UNIT = 'product_unit';
    case PRODUCT_BRAND = 'product_brand';
    case PRODUCT_STOCKS = 'product_stocks';
    case PRODUCT_FLAGS = 'product_flags';
    case PRODUCT_PARAMETERS = 'product_parameters';
    case PRODUCT_URL = 'product_url';
    case PRODUCT_SELLING_DENIED = 'product_selling_denied';
    case PRODUCT_VARIANTS = 'product_variants';
    case PRODUCT_HIDDEN = 'product_hidden';
    case PRODUCT_SELLING_FROM = 'product_selling_from';
    case PRODUCT_SELLING_TO = 'product_selling_to';
    case PRODUCT_PRICE = 'product_price';
    case PRODUCT_CATEGORIES = 'product_categories';
    case CATEGORY_NAME = 'category_name';
    case CATEGORY_TREE = 'category_tree';
    case CATEGORY_ENABLED = 'category_enabled';
}
