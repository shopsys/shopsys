<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Component\ScontoBridge\Transfer\Exception\ScontoBridgeDistributionChannelResolverException;
use App\Model\Country\CountryDataInvalidException;
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
     * @param Country $country
     * @return int
     * @throws ScontoBridgeDistributionChannelResolverException
     */
    public function getDistributionChannelCodeByCountry(Country $country): int
    {
        $code = $country->getCode();
        try {
            $domainId = $this->countryFacade->getDomainIdByCountryCode($code);
        } catch (CountryDataInvalidException $e) {
            throw new ScontoBridgeDistributionChannelResolverException(
                sprintf('Unknown country code \'%s\' for distribution channel code', $code)
            );
        }
        if (array_key_exists($domainId, self::DISTRIBUTION_CODES_BY_DOMAIN) === false) {
            throw new ScontoBridgeDistributionChannelResolverException(
                sprintf('Unknown domain \'%d\' for distribution channel code', $domainId)
            );
        }

        return self::DISTRIBUTION_CODES_BY_DOMAIN[$domainId];
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
