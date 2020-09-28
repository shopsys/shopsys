<?php

declare(strict_types=1);

namespace App\Model\Country;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade as BaseCountryFacade;

class CountryFacade extends BaseCountryFacade
{
    public const COUNTRY_CODES_BY_DOMAIN_ID = [
        Domain::FIRST_DOMAIN_ID => 'CZ',
        Domain::SECOND_DOMAIN_ID => 'SK',
    ];

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public function getCountryOnCurrentDomain(): Country
    {
        $countryCode = self::COUNTRY_CODES_BY_DOMAIN_ID[$this->domain->getId()];
        return $this->countryRepository->findByCode($countryCode);
    }

    /**
     * @param string $code
     * @return int
     * @throws CountryDataInvalidException
     */
    public function getDomainIdByCountryCode(string $code): int
    {
        $domainIds = array_flip(self::COUNTRY_CODES_BY_DOMAIN_ID);
        if (array_key_exists($code, $domainIds) === false) {
            throw new CountryDataInvalidException(sprintf('Unknown country code \'%s\'', $code));
        }

        return $domainIds[$code];
    }
}
