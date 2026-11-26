<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class FriendlyUrlRouteNameEnum extends AbstractEnum
{
    public const string FRONT_ARTICLE_DETAIL = 'front_article_detail';
    public const string FRONT_PRODUCT_DETAIL = 'front_product_detail';
    public const string FRONT_PRODUCT_LIST = 'front_product_list';
    public const string FRONT_BRAND_DETAIL = 'front_brand_detail';
    public const string FRONT_BLOGARTICLE_DETAIL = 'front_blogarticle_detail';
    public const string FRONT_BLOGCATEGORY_DETAIL = 'front_blogcategory_detail';
    public const string FRONT_CATEGORY_SEO = 'front_category_seo';
    public const string FRONT_STORES_DETAIL = 'front_stores_detail';
    public const string FRONT_FLAG_DETAIL = 'front_flag_detail';
}
