<?php

declare(strict_types=1);


namespace App\Model\Product\Brand;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Brand\Brand as BrandEntity;

/**
 * Class CachedBrand
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedBrand extends BrandEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     */
    public function __construct(BrandEntity $brand)
    {
        parent::__construct(new BrandData());
        $this->loadFromParent($brand);
    }
}
