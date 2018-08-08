<?php

namespace Shopsys\FrameworkBundle\Model\Country;

interface CountryDataFactoryInterface
{
    public function create(): CountryData;

    public function createFromCountry(Country $country): CountryData;
}
