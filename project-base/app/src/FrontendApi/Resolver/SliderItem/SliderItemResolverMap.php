<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SliderItem;

use App\Model\Slider\SliderItem;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class SliderItemResolverMap extends ResolverMap
{
    /**
     * @return array<string, array<string, callable>>
     */
    protected function map(): array
    {
        return [
            'SliderItem' => [
                'extendedText' => static function (SliderItem $sliderItem) {
                    return $sliderItem->getSliderExtendedText();
                },
                'extendedTextLink' => static function (SliderItem $sliderItem) {
                    return $sliderItem->getSliderExtendedTextLink();
                },
            ],
        ];
    }
}
