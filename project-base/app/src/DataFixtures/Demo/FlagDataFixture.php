<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Flag\FlagData;
use App\Model\Product\Flag\FlagDataFactory;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;

final class FlagDataFixture extends AbstractReferenceFixture
{
    public const string FLAG_PRODUCT_SALE = 'product_sale';
    public const string FLAG_PRODUCT_ACTION = 'product_action';
    public const string FLAG_PRODUCT_NEW = 'product_new';
    public const string FLAG_PRODUCT_MADEIN_CZ = 'product_madein_cz';
    public const string FLAG_PRODUCT_MADEIN_SK = 'product_madein_sk';
    public const string FLAG_PRODUCT_MADEIN_DE = 'product_madein_de';
    public const string FLAG_PRODUCT_PRICE_HIT = 'product_price_hit';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     */
    public function __construct(
        private readonly FlagFacade $flagFacade,
        private readonly FlagDataFactory $flagDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $flagData = $this->createFlagData('#f7d631');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_SALE);


        $flagData = $this->createFlagData('#ee1c25');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_ACTION);


        $flagData = $this->createFlagData('#2bba51');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('New', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_NEW);


        $flagData = $this->createFlagData('#3110e8');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Made in CZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_MADEIN_CZ);


        $flagData = $this->createFlagData('#b01c1f');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Made in SK', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_MADEIN_SK);


        $flagData = $this->createFlagData('#000000');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Made in DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_MADEIN_DE);


        $flagData = $this->createFlagData('#30a1ba');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $flagData->name[$locale] = t('Price hit', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createFlag($flagData, self::FLAG_PRODUCT_PRICE_HIT);
    }

    /**
     * @param string $rgbColor
     * @return \App\Model\Product\Flag\FlagData
     */
    private function createFlagData(string $rgbColor): FlagData
    {
        $flagData = $this->flagDataFactory->create();

        $flagData->visible = true;
        $flagData->rgbColor = $rgbColor;

        return $flagData;
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @param string $referenceName
     */
    private function createFlag(FlagData $flagData, string $referenceName): void
    {
        $flag = $this->flagFacade->create($flagData);

        $this->addReference($referenceName, $flag);
    }
}
