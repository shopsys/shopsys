<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\NavigationItem;

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail;

class NavigationItemResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    #[Override]
    protected function map(): array
    {
        return [
            'NavigationItem' => [
                'routeName' => static function (NavigationItemDetail $navigationItemDetail) {
                    $routeName = $navigationItemDetail->getNavigationItem()->getRouteName();

                    return $routeName !== null ? FriendlyUrlRouteEnum::tryFrom($routeName) : null;
                },
            ],
        ];
    }
}
