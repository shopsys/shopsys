<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SliderItem;

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use App\Model\Slider\SliderItem;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;

final class SliderItemResolverMap extends ResolverMap
{
    #[Override]
    protected function map(): array
    {
        return [
            'SliderItem' => [
                'routeName' => static function (SliderItem $sliderItem) {
                    $routeName = $sliderItem->getRouteName();

                    return $routeName !== null ? FriendlyUrlRouteEnum::tryFrom($routeName) : null;
                },
            ],
        ];
    }
}
