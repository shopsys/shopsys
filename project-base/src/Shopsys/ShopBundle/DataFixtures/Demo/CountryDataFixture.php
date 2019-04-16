<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class CountryDataFixture extends AbstractReferenceFixture
{
    public const COUNTRY_CZECH_REPUBLIC = 'country_czech_republic';
    public const COUNTRY_SLOVAKIA = 'country_slovakia';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    protected $countryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface
     */
    protected $countryDataFactory;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations
     */
    private $dataFixturesTranslations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface $countryDataFactory
     * @param \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations $dataFixturesTranslations
     */
    public function __construct(
        CountryFacade $countryFacade,
        CountryDataFactoryInterface $countryDataFactory,
        DataFixturesTranslations $dataFixturesTranslations
    ) {
        $this->countryFacade = $countryFacade;
        $this->countryDataFactory = $countryDataFactory;
        $this->dataFixturesTranslations = $dataFixturesTranslations;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $countryData = $this->countryDataFactory->create();
        $countryData->names = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_COUNTRY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::COUNTRY_CZECH_REPUBLIC
        );
        $countryData->code = 'CZ';
        $this->createCountry($countryData, self::COUNTRY_CZECH_REPUBLIC);

        $countryData = $this->countryDataFactory->create();
        $countryData->names = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_COUNTRY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::COUNTRY_SLOVAKIA
        );
        $countryData->code = 'SK';

        $this->createCountry($countryData, self::COUNTRY_SLOVAKIA);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @param string $referenceName
     */
    protected function createCountry(CountryData $countryData, $referenceName): void
    {
        $country = $this->countryFacade->create($countryData);
        $this->addReference($referenceName, $country);
    }
}
