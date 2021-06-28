<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

class UnitDataFixture extends AbstractReferenceFixture
{
    public const UNIT_CUBIC_METERS = 'unit_m3';
    public const UNIT_PIECES = 'unit_pcs';
    public const UNIT_GRAM = 'unit_gram';
    public const UNIT_CENTIMETER = 'unit_centimeter';
    public const UNIT_INCH = 'unit_inch';
    public const UNIT_TON = 'unit_ton';
    public const UNIT_KILOWATT = 'unit_kilowatt';
    public const UNIT_KILOGRAM = 'unit_kilogram';
    public const UNIT_WATT = 'unit_watt';
    public const UNIT_VOLT = 'unit_volt';
    public const UNIT_METER = 'unit_meter';

    /**
     * @var \App\Model\Product\Unit\UnitFacade
     */
    private $unitFacade;

    /**
     * @var \App\Model\Product\Unit\UnitDataFactory
     */
    private $unitDataFactory;

    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Model\Product\Unit\UnitFacade $unitFacade
     * @param \App\Model\Product\Unit\UnitDataFactory $unitDataFactory
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        UnitFacade $unitFacade,
        UnitDataFactoryInterface $unitDataFactory,
        Setting $setting,
        Domain $domain
    ) {
        $this->unitFacade = $unitFacade;
        $this->unitDataFactory = $unitDataFactory;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $unitData = $this->unitDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('m³', [], 'dataFixtures', $locale);
        }
        $this->createUnit($unitData, self::UNIT_CUBIC_METERS);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('pcs', [], 'dataFixtures', $locale);
        }
        $this->createUnit($unitData, self::UNIT_PIECES);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('g', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'GRAM';
        $this->createUnit($unitData, self::UNIT_GRAM);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('cm', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'CENTIMETER';
        $this->createUnit($unitData, self::UNIT_CENTIMETER);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('in', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'INCH';
        $this->createUnit($unitData, self::UNIT_INCH);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('t', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'TON';
        $this->createUnit($unitData, self::UNIT_TON);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('kW', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'KILOWATT';
        $this->createUnit($unitData, self::UNIT_KILOWATT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('kg', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'KILOGRAM';
        $this->createUnit($unitData, self::UNIT_KILOGRAM);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('W', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'WATT';
        $this->createUnit($unitData, self::UNIT_WATT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('V', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'VOLT';
        $this->createUnit($unitData, self::UNIT_VOLT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('m', [], 'dataFixtures', $locale);
        }
        $unitData->akeneoCode = 'METER';
        $this->createUnit($unitData, self::UNIT_METER);

        $this->setPiecesAsDefaultUnit();
    }

    /**
     * @param \App\Model\Product\Unit\UnitData $unitData
     * @param string|null $referenceName
     */
    private function createUnit(UnitData $unitData, ?string $referenceName = null)
    {
        $unit = $this->unitFacade->create($unitData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $unit);
        }
    }

    private function setPiecesAsDefaultUnit(): void
    {
        /** @var \App\Model\Product\Unit\Unit $defaultUnit */
        $defaultUnit = $this->getReference(self::UNIT_PIECES);
        $this->setting->set(Setting::DEFAULT_UNIT, $defaultUnit->getId());
    }
}
