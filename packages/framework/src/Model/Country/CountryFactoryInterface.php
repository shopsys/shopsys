<?php

namespace Shopsys\FrameworkBundle\Model\Country;

interface CountryFactoryInterface
{
    public function create(CountryData $data, int $domainId): Country;
}
