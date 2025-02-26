<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider as BaseProductExportFieldProvider;

class ProductExportFieldProvider extends BaseProductExportFieldProvider
{
    public const string MAIN_CATEGORY_PATH = 'main_category_path';
    public const string USPS = 'usps';
    public const string SEARCHING_NAMES = 'searching_names';
    public const string SEARCHING_DESCRIPTIONS = 'searching_descriptions';
    public const string SEARCHING_CATNUMS = 'searching_catnums';
    public const string SEARCHING_EANS = 'searching_eans';
    public const string SEARCHING_PARTNOS = 'searching_partnos';
    public const string SEARCHING_SHORT_DESCRIPTIONS = 'searching_short_descriptions';
    public const string RELATED_PRODUCTS = 'related_products';
    public const string BREADCRUMB = 'breadcrumb';
}
