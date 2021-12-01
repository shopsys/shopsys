<?php

declare(strict_types=1);

namespace App\Model\Product\Unit;

use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData as BaseUnitData;

class UnitData extends BaseUnitData
{
    /**
     * @var string|null
     */
    public ?string $akeneoCode = null;
}
