<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\ParameterGroupTranslation as ParameterGroupTranslationEntity;

/**
 * Class CachedParameterGroupTranslation
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterGroupTranslation extends ParameterGroupTranslationEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupTranslation $parameterGroupTranslation
     */
    public function __construct(ParameterGroupTranslationEntity $parameterGroupTranslation)
    {
        $this->loadFromParent($parameterGroupTranslation);
        $this->translatable = null;
    }
}
