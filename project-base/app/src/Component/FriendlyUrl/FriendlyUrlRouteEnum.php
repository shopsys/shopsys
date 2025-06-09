<?php

declare(strict_types=1);

namespace App\Component\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;

enum FriendlyUrlRouteEnum: string
{
    case FRONT_ARTICLE_DETAIL = 'front_article_detail';
    case FRONT_PRODUCT_DETAIL = 'front_product_detail';
    case FRONT_PRODUCT_LIST = 'front_product_list';
    case FRONT_BRAND_DETAIL = 'front_brand_detail';
    case FRONT_BLOGARTICLE_DETAIL = 'front_blogarticle_detail';
    case FRONT_BLOGCATEGORY_DETAIL = 'front_blogcategory_detail';
    case FRONT_CATEGORY_SEO = 'front_category_seo';
    case FRONT_STORES_DETAIL = 'front_stores_detail';
    case FRONT_FLAG_DETAIL = 'front_flag_detail';

    /**
     * @param \App\Component\FriendlyUrl\FriendlyUrlRouteEnum $case
     * @return array
     */
    public static function getOptionsForCase(self $case): array
    {
        return match ($case) {
            self::FRONT_ARTICLE_DETAIL, self::FRONT_STORES_DETAIL => [
                FriendlyUrlRouter::ROUTE_OPTION_MULTIDOMAIN => false,
            ],
            default => [],
        };
    }
}
