<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class UnitDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    protected function createInstance(): UnitData
    {
        return new UnitData();
    }

    public function create(): UnitData
    {
        $unitData = $this->createInstance();
        $this->fillNew($unitData);

        return $unitData;
    }

    protected function fillNew(UnitData $unitData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = null;
        }
    }

    public function createFromUnit(Unit $unit): UnitData
    {
        $unitData = $this->createInstance();
        $this->fillFromUnit($unitData, $unit);

        return $unitData;
    }

    protected function fillFromUnit(UnitData $unitData, Unit $unit)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation[] $translations */
        $translations = $unit->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $unitData->name = $names;
    }
}
