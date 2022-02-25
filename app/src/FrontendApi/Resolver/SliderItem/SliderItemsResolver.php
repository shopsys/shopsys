<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SliderItem;

use App\Model\Slider\SliderItemFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class SliderItemsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Slider\SliderItemFacade
     */
    private SliderItemFacade $sliderItemFacade;

    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     */
    public function __construct(SliderItemFacade $sliderItemFacade)
    {
        $this->sliderItemFacade = $sliderItemFacade;
    }

    /**
     * @return \App\Model\Slider\SliderItem[]
     */
    public function resolve(): array
    {
        return $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'sliderItems'];
    }
}
