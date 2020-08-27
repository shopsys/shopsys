<?php

declare(strict_types=1);


namespace App\Model\Product\Flag;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Flag\Flag as FlagEntity;

/**
 * Class CachedFlag
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedFlag extends FlagEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     */
    public function __construct(FlagEntity $flag)
    {
        parent::__construct(new FlagData());

        $this->loadFromParent($flag);
    }
}
