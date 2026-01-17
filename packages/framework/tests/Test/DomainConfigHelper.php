<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class DomainConfigHelper
{
    public const string DEFAULT_EXAMPLE_COM_BASE_URL = 'https://example.com:8080';
    public const string DEFAULT_TIMEZONE_STRING = 'Europe/Prague';

    public static function getDomainConfig(
        int $id = 1,
        string $url = self::DEFAULT_EXAMPLE_COM_BASE_URL,
        string $name = 'example.com',
        string $locale = 'cs',
        string $dateTimeZoneString = self::DEFAULT_TIMEZONE_STRING,
        string $baseUrl = self::DEFAULT_EXAMPLE_COM_BASE_URL,
        string $type = DomainConfig::TYPE_B2C,
        bool $loadDemoData = true,
        ?string $postfix = null,
    ): DomainConfig {
        return new DomainConfig($id, $url, $name, $locale, new DateTimeZone($dateTimeZoneString), $baseUrl, $type, $loadDemoData, $postfix);
    }
}
