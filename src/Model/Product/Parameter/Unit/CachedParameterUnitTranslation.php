<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter\Unit;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\Unit\ParameterUnitTranslation as ParameterUnitTranslationEntity;

/**
 * Class CachedParameterUnitTranslation
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterUnitTranslation extends ParameterUnitTranslationEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitTranslation $parameterUnitTranslation
     */
    public function __construct(ParameterUnitTranslationEntity $parameterUnitTranslation)
    {
        $this->loadFromParent($parameterUnitTranslation);
        $this->translatable = null;
    }
}
