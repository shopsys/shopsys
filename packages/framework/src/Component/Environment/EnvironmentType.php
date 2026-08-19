<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Environment;

class EnvironmentType
{
    public const DEVELOPMENT = 'dev';
    public const PRODUCTION = 'prod';
    public const TEST = 'test';

    public const ALL = [self::DEVELOPMENT, self::PRODUCTION, self::TEST];

    public static function isDebug(string $environment): bool
    {
        return $environment === self::DEVELOPMENT;
    }
}
