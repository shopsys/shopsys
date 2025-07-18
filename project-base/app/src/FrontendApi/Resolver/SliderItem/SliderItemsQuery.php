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
        // === SERVICE INITIALIZATION LOGGING ===
        error_log("🔍 [SLIDER_SERVICE] Starting query execution");
        error_log("🔍 [SLIDER_SERVICE] SliderItemFacade available: " . ($this->sliderItemFacade ? 'YES' : 'NO'));
        
        // === TIMING MEASUREMENT ===
        $startTime = microtime(true);
        
        try {
            $result = $this->sliderItemFacade->getAllVisibleOnCurrentDomain();
            $queryExecutionTime = (microtime(true) - $startTime) * 1000;
            
            error_log("🔍 [SLIDER_TIMING] Facade query execution: {$queryExecutionTime}ms");
            error_log("🔍 [SLIDER_RESULT] Final result count: " . count($result));
            
            return $result;
            
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;
            error_log("🔍 [SLIDER_TIMING] Total execution time (failed): {$totalTime}ms");
            error_log("🚨 [SLIDER_ERROR] Query failed: " . $e->getMessage());
            error_log("🚨 [SLIDER_ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [];
        }
    }
}
