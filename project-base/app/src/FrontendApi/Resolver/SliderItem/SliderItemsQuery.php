<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SliderItem;

use App\Model\Slider\SliderItemFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SliderItemsQuery extends AbstractQuery
{
    public function __construct(private readonly SliderItemFacade $sliderItemFacade)
    {
    }

    /**
     * @return \App\Model\Slider\SliderItem[]
     */
    public function sliderItemsQuery(): array
    {
        return $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
    }
}
