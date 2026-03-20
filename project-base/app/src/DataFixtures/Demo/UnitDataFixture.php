<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactory;
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
    public const string UNIT_ISO = 'unit_iso';
    public const string UNIT_MEGABYTE = 'unit_megabyte';
    public const string UNIT_GIGAHERTZ = 'unit_gigahertz';
    public const string UNIT_BAR = 'unit_bar';
    public const string UNIT_MILLILITER = 'unit_milliliter';
    public const string UNIT_LITER = 'unit_liter';
    public const string UNIT_MEGAPIXEL = 'unit_megapixel';
    public const string UNIT_KILOWATT_HOURS = 'unit_kilowatt_hours';
    public const string UNIT_YEARS = 'unit_years';

    public function __construct(
        private readonly UnitFacade $unitFacade,
        private readonly UnitDataFactory $unitDataFactory,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $unitData = $this->unitDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('m³', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_CUBIC_METERS);

        // default unit is created in migration Version20260320122217, so we only need to add reference to it
        $this->addReference(self::UNIT_PIECES, $this->unitFacade->getDefaultUnit());

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('g', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_GRAM);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('cm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_CENTIMETER);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('in', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_INCH);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('t', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_TON);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('kW', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_KILOWATT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_KILOGRAM);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('W', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_WATT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('V', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_VOLT);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('m', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_METER);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('ISO', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_ISO);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_MEGABYTE);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('GHz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_GIGAHERTZ);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('bar', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_BAR);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('ml', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_MILLILITER);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('l', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_LITER);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('Mpx', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_MEGAPIXEL);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('kWh', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_KILOWATT_HOURS);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_YEARS);
    }

    private function createUnit(UnitData $unitData, ?string $referenceName = null): void
    {
        $unit = $this->unitFacade->create($unitData);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $unit);
        }
    }
}
