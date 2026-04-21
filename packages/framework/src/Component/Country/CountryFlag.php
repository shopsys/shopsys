<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Country;

use Symfony\Component\Intl\Countries;

class CountryFlag
{
    public static function getFlagEmoji(string $countryCode): string
    {
        $countryCodeUppercase = mb_strtoupper($countryCode, 'UTF-8');

        if (!Countries::exists($countryCodeUppercase) || !preg_match('/^[A-Z]{2}$/', $countryCodeUppercase)) {
            return '';
        }

        $base = 0x1F1E6;

        return mb_chr($base + ord($countryCodeUppercase[0]) - ord('A'))
            . mb_chr($base + ord($countryCodeUppercase[1]) - ord('A'));
    }
}
