<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

class UnitDataFixture extends AbstractReferenceFixture
{
    public const string UNIT_CUBIC_METERS = 'unit_m3';
    public const string UNIT_PIECES = 'unit_pcs';
    public const string UNIT_GRAM = 'unit_gram';
    public const string UNIT_CENTIMETER = 'unit_centimeter';
    public const string UNIT_INCH = 'unit_inch';
    public const string UNIT_TON = 'unit_ton';
    public const string UNIT_KILOWATT = 'unit_kilowatt';
    public const string UNIT_KILOGRAM = 'unit_kilogram';
    public const string UNIT_WATT = 'unit_watt';
    public const string UNIT_VOLT = 'unit_volt';
    public const string UNIT_METER = 'unit_meter';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactory $unitDataFactory
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(
        private readonly UnitFacade $unitFacade,
        private readonly UnitDataFactoryInterface $unitDataFactory,
        private readonly Setting $setting,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $unitData = $this->unitDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('m³', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_CUBIC_METERS);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_PIECES);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_GRAM);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_CENTIMETER);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_INCH);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('t', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_TON);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('kW', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_KILOWATT);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_KILOGRAM);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_WATT);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('V', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_VOLT);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $unitData->name[$locale] = t('m', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_METER);

        $this->setPiecesAsDefaultUnit();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     * @param string|null $referenceName
     */
    private function createUnit(UnitData $unitData, ?string $referenceName = null): void
    {
        $unit = $this->unitFacade->create($unitData);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $unit);
        }
    }

    private function setPiecesAsDefaultUnit(): void
    {
        $defaultUnit = $this->getReference(self::UNIT_PIECES, Unit::class);
        $this->setting->set(Setting::DEFAULT_UNIT, $defaultUnit->getId());
    }
}
