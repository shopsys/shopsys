<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountryDataFixture extends AbstractReferenceFixture
{
    const COUNTRY_CZECH_REPUBLIC_2 = 'country_czech_republic_2';
    const COUNTRY_SLOVAKIA_2 = 'country_slovakia_2';

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
        $domainId = 2;
        $countryData = $this->countryDataFactory->create();
        $countryData->name = 'Česká republika';
        $countryData->code = 'CZ';
        $this->createCountry($countryData, $domainId, self::COUNTRY_CZECH_REPUBLIC_2);

        $domainId = 2;
        $countryData = $this->countryDataFactory->create();
        $countryData->name = 'Slovenská republika';
        $countryData->code = 'SK';
        $this->createCountry($countryData, $domainId, self::COUNTRY_SLOVAKIA_2);
    }
    
    private function createCountry(CountryData $countryData, int $domainId, string $referenceName): void
    {
        $country = $this->countryFacade->create($countryData, $domainId);
        $this->addReference($referenceName, $country);
    }
}
