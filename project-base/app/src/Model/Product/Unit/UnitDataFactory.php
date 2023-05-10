<?php

declare(strict_types=1);

namespace App\Model\Product\Unit;

use Shopsys\FrameworkBundle\Model\Product\Unit\Unit as BaseUnit;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData as BaseUnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactory as BaseUnitDataFactory;

/**
 * @method \App\Model\Product\Unit\UnitData create()
 * @method \App\Model\Product\Unit\UnitData createFromUnit(\App\Model\Product\Unit\Unit $unit)
 * @method fillNew(\App\Model\Product\Unit\UnitData $unitData)
 */
class UnitDataFactory extends BaseUnitDataFactory
{
    /**
     * @return \App\Model\Product\Unit\UnitData
     */
    protected function createInstance(): UnitData
    {
        return new UnitData();
    }

    /**
     * @param \App\Model\Product\Unit\UnitData $unitData
     * @param \App\Model\Product\Unit\Unit $unit
     */
    protected function fillFromUnit(BaseUnitData $unitData, BaseUnit $unit)
    {
        parent::fillFromUnit($unitData, $unit);

        $unitData->akeneoCode = $unit->getAkeneoCode();
    }
}
