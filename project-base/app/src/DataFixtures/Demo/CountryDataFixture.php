<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountryDataFixture extends AbstractReferenceFixture
{
    public const string COUNTRY_CZECH_REPUBLIC = 'country_czech_republic';
    public const string COUNTRY_SLOVAKIA = 'country_slovakia';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryDataFactory $countryDataFactory
     */
    public function __construct(
        private readonly CountryFacade $countryFacade,
        private readonly CountryDataFactoryInterface $countryDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $countryData = $this->countryDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $countryData->names[$locale] = t('Czech republic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $countryData->code = 'CZ';
        $this->createCountry($countryData, self::COUNTRY_CZECH_REPUBLIC);

        $countryData = $this->countryDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $countryData->names[$locale] = t('Slovakia', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $countryData->code = 'SK';

        $this->createCountry($countryData, self::COUNTRY_SLOVAKIA);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @param string $referenceName
     */
    private function createCountry(CountryData $countryData, string $referenceName): void
    {
        $country = $this->countryFacade->create($countryData);
        $this->addReference($referenceName, $country);
    }
}
