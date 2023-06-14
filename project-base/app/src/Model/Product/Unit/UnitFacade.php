<?php

declare(strict_types=1);

namespace App\Model\Product\Unit;

use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade as BaseUnitFacade;

/**
 * @property \App\Model\Product\Unit\UnitRepository $unitRepository
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Unit\UnitRepository $unitRepository, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFactory $unitFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
 * @method \App\Model\Product\Unit\Unit getById(int $unitId)
 * @method \App\Model\Product\Unit\Unit create(\App\Model\Product\Unit\UnitData $unitData)
 * @method \App\Model\Product\Unit\Unit edit(int $unitId, \App\Model\Product\Unit\UnitData $unitData)
 * @method \App\Model\Product\Unit\Unit[] getAll()
 * @method bool isUnitUsed(\App\Model\Product\Unit\Unit $unit)
 * @method \App\Model\Product\Unit\Unit[] getAllExceptId(int $unitId)
 * @method \App\Model\Product\Unit\Unit getDefaultUnit()
 * @method setDefaultUnit(\App\Model\Product\Unit\Unit $unit)
 * @method bool isUnitDefault(\App\Model\Product\Unit\Unit $unit)
 * @method dispatchUnitEvent(\App\Model\Product\Unit\Unit $unit, string $eventType)
 */
class UnitFacade extends BaseUnitFacade
{
    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Unit
    {
        return $this->unitRepository->findByAkeneoCode($akeneoCode);
    }
}
