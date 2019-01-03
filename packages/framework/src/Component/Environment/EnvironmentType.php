<?php

namespace Shopsys\FrameworkBundle\Component\Environment;

class EnvironmentType
{
    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'prod';
    const TEST = 'test';
    const GOOGLE_CLOUD = 'google_cloud';

    const ALL = [self::DEVELOPMENT, self::PRODUCTION, self::TEST, self::GOOGLE_CLOUD,];

    /**
     * @param string $environment
     * @return bool
     */
    public static function isDebug(string $environment): bool
    {
        return $environment === self::DEVELOPMENT;
    }
}
