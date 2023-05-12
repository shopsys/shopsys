<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

class UnitDataFixture extends AbstractReferenceFixture
{
    public const UNIT_CUBIC_METERS = 'unit_m3';
    public const UNIT_PIECES = 'unit_pcs';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface $unitDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly UnitFacade $unitFacade,
        private readonly UnitDataFactoryInterface $unitDataFactory,
        private readonly Setting $setting,
        private readonly Domain $domain
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $unitData = $this->unitDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('m³', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_CUBIC_METERS);

        foreach ($this->domain->getAllLocales() as $locale) {
            $unitData->name[$locale] = t('pcs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $this->createUnit($unitData, self::UNIT_PIECES);

        $this->setPiecesAsDefaultUnit();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     * @param string|null $referenceName
     */
    private function createUnit(UnitData $unitData, $referenceName = null)
    {
        $unit = $this->unitFacade->create($unitData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $unit);
        }
    }

    private function setPiecesAsDefaultUnit(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $defaultUnit */
        $defaultUnit = $this->getReference(self::UNIT_PIECES);
        $this->setting->set(Setting::DEFAULT_UNIT, $defaultUnit->getId());
    }
}
