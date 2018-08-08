<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

class UnitDataFactory implements UnitDataFactoryInterface
{
    public function create(): UnitData
    {
        return new UnitData();
    }

    public function createFromUnit(Unit $unit): UnitData
    {
        $unitData = new UnitData();
        $this->fillFromUnit($unitData, $unit);

        return $unitData;
    }

    protected function fillFromUnit(UnitData $unitData, Unit $unit)
    {
        $translations = $unit->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $unitData->name = $names;
    }
}
