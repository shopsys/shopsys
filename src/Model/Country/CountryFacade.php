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
    public const PHONE_PREFIX_BY_DOMAIN_ID = [
        Domain::FIRST_DOMAIN_ID => 420,
        Domain::SECOND_DOMAIN_ID => 421,
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

    /**
     * @param int $domainId
     * @return int
     * @throws CountryDataInvalidException
     */
    public function getPhonePrefixByDomainId(int $domainId): int
    {
        if (array_key_exists($domainId, self::PHONE_PREFIX_BY_DOMAIN_ID) === false) {
            throw new CountryDataInvalidException('Unkown domain id \'%d\'', $domainId);
        }

        return self::PHONE_PREFIX_BY_DOMAIN_ID[$domainId];
    }
}
