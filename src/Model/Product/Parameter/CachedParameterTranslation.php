<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter;

use App\Model\LoadFromParentTrait;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation as ParameterTranslationEntity;

/**
 * Class CachedParameterTranslation
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterTranslation extends ParameterTranslationEntity
{
    use LoadFromParentTrait;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation $parameterTranslation
     */
    public function __construct(ParameterTranslationEntity $parameterTranslation)
    {
        $this->loadFromParent($parameterTranslation);
        $this->translatable = null;
    }
}
