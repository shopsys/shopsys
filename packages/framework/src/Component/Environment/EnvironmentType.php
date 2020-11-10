<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Environment;

class EnvironmentType
{
    public const DEVELOPMENT = 'dev';
    public const PRODUCTION = 'prod';
    public const TEST = 'test';
    public const ACCEPTANCE = 'acc';

    public const ALL = [self::DEVELOPMENT, self::PRODUCTION, self::TEST, self::ACCEPTANCE];

    /**
     * @param string $environment
     * @return bool
     */
    public static function isDebug(string $environment): bool
    {
        return $environment === self::DEVELOPMENT;
    }
}
