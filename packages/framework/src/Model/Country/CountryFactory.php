<?php

namespace Shopsys\FrameworkBundle\Model\Country;

class CountryFactory implements CountryFactoryInterface
{
    public function create(CountryData $data, int $domainId): Country
    {
        return new Country($data, $domainId);
    }
}
