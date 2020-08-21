<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\ParameterValue as ParameterValueEntity;

/**
 * Class CachedParameterValue
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterValue extends ParameterValueEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    public function __construct(ParameterValueEntity $parameterValue)
    {
        parent::__construct(new ParameterValueData());
        $this->loadFromParent($parameterValue);
    }
}
