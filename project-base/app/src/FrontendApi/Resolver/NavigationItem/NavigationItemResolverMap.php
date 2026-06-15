<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\NavigationItem;

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail;

class NavigationItemResolverMap extends ResolverMap
{
    #[Override]
    protected function map(): array
    {
        return [
            'NavigationItem' => [
                /**
                 * routeName is on purpose defined and resolved in project-base
                 * because it works with FriendlyUrlRouteEnum that serves as a config and must be defined in project-base as well.
                 */
                'routeName' => static function (NavigationItemDetail $navigationItemDetail) {
                    $routeName = $navigationItemDetail->getNavigationItem()->getRouteName();

                    return $routeName !== null ? FriendlyUrlRouteEnum::tryFrom($routeName) : null;
                },
            ],
        ];
    }
}
