<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;

class ScontoBridgeDistributionChannelResolver
{
    public const DISTRIBUTION_CHANEL_CODE_CZ = 421;
    public const DISTRIBUTION_CHANEL_CODE_SK = 422;
    private const DISTRIBUTION_CODES_BY_DOMAIN = [
        Domain::FIRST_DOMAIN_ID => self::DISTRIBUTION_CHANEL_CODE_CZ,
        Domain::SECOND_DOMAIN_ID => self::DISTRIBUTION_CHANEL_CODE_SK
    ];

    /**
     * @var CountryFacade
     */
    private CountryFacade $countryFacade;

    public function __construct(CountryFacade $countryFacade)
    {
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param int|null $countryString
     * @return int|null
     */
    public function getDomainIdByDistributionChannelCode(?int $countryString): ?int
    {
        $codesByDomain = array_flip(self::DISTRIBUTION_CODES_BY_DOMAIN);

        return $codesByDomain[$countryString] ?? null;
    }

    /**
     * @param int|null $distributionChannelCode
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountryByDistributionChannelCode(?int $distributionChannelCode): ?Country
    {
        $domainId = $this->getDomainIdByDistributionChannelCode($distributionChannelCode);
        if ($domainId !== null) {
            return $this->countryFacade->findByCode(CountryFacade::COUNTRY_CODES_BY_DOMAIN_ID[$domainId]);
        }

        return null;
    }
}
