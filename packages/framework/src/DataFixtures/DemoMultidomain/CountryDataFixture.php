<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountryDataFixture extends AbstractReferenceFixture
{
    const COUNTRY_CZECH_REPUBLIC_2 = 'country_czech_republic_2';
    const COUNTRY_SLOVAKIA_2 = 'country_slovakia_2';

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $domainId = 2;
        $countryData = new CountryData();
        $countryData->name = 'Česká republika';
        $this->createCountry($countryData, $domainId, self::COUNTRY_CZECH_REPUBLIC_2);

        $domainId = 2;
        $countryData = new CountryData();
        $countryData->name = 'Slovenská republika';
        $this->createCountry($countryData, $domainId, self::COUNTRY_SLOVAKIA_2);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @param int $domainId
     * @param string $referenceName
     */
    private function createCountry(CountryData $countryData, $domainId, $referenceName)
    {
        $countryFacade = $this->get(CountryFacade::class);
        /* @var $countryFacade \Shopsys\FrameworkBundle\Model\Country\CountryFacade */

        $country = $countryFacade->create($countryData, $domainId);
        $this->addReference($referenceName, $country);
    }
}
