<?php

declare(strict_types=1);

namespace App\Model\Product\Unit;

use Shopsys\FrameworkBundle\Model\Product\Unit\UnitRepository as BaseUnitRepository;

/**
 * @method \App\Model\Product\Unit\Unit|null findById(int $unitId)
 * @method \App\Model\Product\Unit\Unit getById(int $unitId)
 * @method \App\Model\Product\Unit\Unit[] getAll()
 * @method \App\Model\Product\Unit\Unit[] getAllExceptId(int $unitId)
 * @method bool existsProductWithUnit(\App\Model\Product\Unit\Unit $unit)
 * @method replaceUnit(\App\Model\Product\Unit\Unit $oldUnit, \App\Model\Product\Unit\Unit $newUnit)
 */
class UnitRepository extends BaseUnitRepository
{
    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Unit
    {
        return $this->getUnitRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }
}
