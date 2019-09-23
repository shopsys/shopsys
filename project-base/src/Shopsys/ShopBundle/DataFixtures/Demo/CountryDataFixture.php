<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryDataFactoryInterface $countryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(CountryFacade $countryFacade, CountryDataFactoryInterface $countryDataFactory, Domain $domain)
    {
        $this->countryFacade = $countryFacade;
        $this->countryDataFactory = $countryDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $countryData = $this->countryDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $countryData->names[$locale] = t('Czech republic', [], 'dataFixtures', $locale);
        }

        $countryData->code = 'CZ';
        $this->createCountry($countryData, self::COUNTRY_CZECH_REPUBLIC);

        $countryData = $this->countryDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $countryData->names[$locale] = t('Slovakia', [], 'dataFixtures', $locale);
        }

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
