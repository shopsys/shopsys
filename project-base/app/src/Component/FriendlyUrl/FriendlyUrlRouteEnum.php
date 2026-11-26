<?php

declare(strict_types=1);

namespace App\Component\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouteNameEnum;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;

enum FriendlyUrlRouteEnum: string
{
    case FRONT_ARTICLE_DETAIL = FriendlyUrlRouteNameEnum::FRONT_ARTICLE_DETAIL;
    case FRONT_PRODUCT_DETAIL = FriendlyUrlRouteNameEnum::FRONT_PRODUCT_DETAIL;
    case FRONT_PRODUCT_LIST = FriendlyUrlRouteNameEnum::FRONT_PRODUCT_LIST;
    case FRONT_BRAND_DETAIL = FriendlyUrlRouteNameEnum::FRONT_BRAND_DETAIL;
    case FRONT_BLOGARTICLE_DETAIL = FriendlyUrlRouteNameEnum::FRONT_BLOGARTICLE_DETAIL;
    case FRONT_BLOGCATEGORY_DETAIL = FriendlyUrlRouteNameEnum::FRONT_BLOGCATEGORY_DETAIL;
    case FRONT_CATEGORY_SEO = FriendlyUrlRouteNameEnum::FRONT_CATEGORY_SEO;
    case FRONT_STORES_DETAIL = FriendlyUrlRouteNameEnum::FRONT_STORES_DETAIL;
    case FRONT_FLAG_DETAIL = FriendlyUrlRouteNameEnum::FRONT_FLAG_DETAIL;

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
