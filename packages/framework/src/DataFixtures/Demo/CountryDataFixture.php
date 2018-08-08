<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountryDataFixture extends AbstractReferenceFixture
{
    const COUNTRY_CZECH_REPUBLIC_1 = 'country_czech_republic_1';
    const COUNTRY_SLOVAKIA_1 = 'country_slovakia_1';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface
     */
    private $countryDataFactory;

    public function __construct(CountryFacade $countryFacade, CountryDataFactoryInterface $countryDataFactory)
    {
        $this->countryFacade = $countryFacade;
        $this->countryDataFactory = $countryDataFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $domainId = 1;
        $countryData = $this->countryDataFactory->create();
        $countryData->name = 'Czech republic';
        $countryData->code = 'CZ';
        $this->createCountry($countryData, $domainId, self::COUNTRY_CZECH_REPUBLIC_1);

        $countryData = $this->countryDataFactory->create();
        $countryData->name = 'Slovakia';
        $countryData->code = 'SK';
        $this->createCountry($countryData, $domainId, self::COUNTRY_SLOVAKIA_1);
    }

    private function createCountry(CountryData $countryData, int $domainId, string $referenceName): void
    {
        $country = $this->countryFacade->create($countryData, $domainId);
        $this->addReference($referenceName, $country);
    }
}
