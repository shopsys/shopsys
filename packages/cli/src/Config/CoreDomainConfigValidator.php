<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use DateTimeZone;
use Exception;
use RuntimeException;

final class CoreDomainConfigValidator
{
    /**
     * @var string[]
     */
    public const array SUPPORTED_DOMAIN_TYPES = ['b2c', 'b2b'];

    public static function validateDomainName(string $value): string
    {
        if (trim($value) === '') {
            throw new RuntimeException('Domain name cannot be empty');
        }

        return $value;
    }

    public static function validateLocale(string $value): string
    {
        if (mb_strlen(trim($value)) !== 2) {
            throw new RuntimeException(sprintf('Locale has to be exactly two characters long, "%s" provided', $value));
        }

        return strtolower($value);
    }

    public static function validateTimeZone(string $value): string
    {
        try {
            new DateTimeZone($value);
        } catch (Exception) {
            throw new RuntimeException(sprintf('Invalid timezone: "%s"', $value));
        }

        return $value;
    }

    public static function validateDomainType(string $value): string
    {
        if (!in_array($value, self::SUPPORTED_DOMAIN_TYPES, true)) {
            throw new RuntimeException(sprintf(
                'Domain type must be one of: %s. Got: %s',
                implode(', ', self::SUPPORTED_DOMAIN_TYPES),
                $value,
            ));
        }

        return $value;
    }

    public static function validateCurrencyCode(string $value): string
    {
        if (!preg_match('/^[A-Za-z]{3}$/', $value)) {
            throw new RuntimeException('Currency code must be a 3-letter ISO code (e.g., EUR, CZK)');
        }

        return strtoupper($value);
    }
}
