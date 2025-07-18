<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SliderItem;

use App\Model\Slider\SliderItemFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SliderItemsQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     */
    public function __construct(private readonly SliderItemFacade $sliderItemFacade)
    {
    }

    /**
     * @return \App\Model\Slider\SliderItem[]
     */
    public function sliderItemsQuery(): array
    {
        error_log("🔍 [SliderItemsQuery] Starting query execution");
        
        $result = $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
        
        error_log("🔍 [SliderItemsQuery] Final result count: " . count($result));
        return $result;
    }
}
