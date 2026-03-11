<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix;

use libphonenumber\PhoneNumberUtil;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefix;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixRepository;
use Symfony\Component\Intl\Countries;

class CountryDialCodeProvider
{
    protected const string CACHE_NAMESPACE = 'country_dial_codes';

    // ZZ is reserved ISO 3166-1 alpha-2 code for unknown country
    public const string UNKNOWN_COUNTRY_CODE = 'ZZ';

    public function __construct(
        protected readonly PhonePrefixRepository $phonePrefixRepository,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]
     */
    public function getAll(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::CACHE_NAMESPACE,
            function () {
                $phoneUtil = PhoneNumberUtil::getInstance();

                // Filter out regions that are not recognized by Symfony's Intl component, as they cannot be used in the admin form.
                $regions = array_filter(
                    $phoneUtil->getSupportedRegions(),
                    static fn (string $code): bool => Countries::exists($code),
                );

                sort($regions);

                return array_map(
                    static fn (string $code): CountryDialCode => new CountryDialCode($code, '+' . $phoneUtil->getCountryCodeForRegion($code)),
                    $regions,
                );
            },
            'all',
        );
    }

    public function getDialCodeForCountryCode(string $countryCode): ?string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $code = $phoneUtil->getCountryCodeForRegion($countryCode);

        return $code !== 0 ? '+' . $code : null;
    }

    public function getCountryCodeForDialCode(?string $dialCode): ?string
    {
        if ($dialCode === null) {
            return null;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $countryCallingCode = ltrim($dialCode, '+');

        $countryCode = $phoneUtil->getRegionCodeForCountryCode((int)$countryCallingCode);

        return $countryCode !== self::UNKNOWN_COUNTRY_CODE ? $countryCode : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]
     */
    public function getAllEnabledOnDomain(int $domainId): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        return array_map(
            static fn (PhonePrefix $phonePrefixSetting): CountryDialCode => new CountryDialCode($phonePrefixSetting->getCode(), '+' . $phoneUtil->getCountryCodeForRegion($phonePrefixSetting->getCode())),
            $this->phonePrefixRepository->findAllByDomainId($domainId),
        );
    }
}
