<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix;

use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;

class CountryDialCodeProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]
     */
    public function getAll(): array
    {
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
    }
}
