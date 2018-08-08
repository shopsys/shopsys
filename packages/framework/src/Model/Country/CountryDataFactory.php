<?php

namespace Shopsys\FrameworkBundle\Model\Country;

class CountryDataFactory implements CountryDataFactoryInterface
{
    public function create(): CountryData
    {
        return new CountryData();
    }

    public function createFromCountry(Country $country): CountryData
    {
        $countryData = new CountryData();
        $this->fillFromCountry($countryData, $country);

        return $countryData;
    }

    protected function fillFromCountry(CountryData $countryData, Country $country): void
    {
        $countryData->name = $country->getName();
        $countryData->code = $country->getCode();
    }
}
