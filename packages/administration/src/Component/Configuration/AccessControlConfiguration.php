<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Configuration;

/**
 * Configuration object for access control settings
 */
final readonly class AccessControlConfiguration
{
    /**
     * Default routes that are always excluded from access control checks
     */
    private const array DEFAULT_EXCLUDED_ROUTES = [
        '2fa_login',
        '2fa_login_check',
        'admin_login',
        'admin_login_check',
        'admin_logout',
        'ef_connect',
        'ef_main_js',
        'elfinder',
        'ux_live_component',
    ];

    /**
     * @param string[] $additionalExcludedRouteNames
     */
    public function __construct(
        private array $additionalExcludedRouteNames = [],
    ) {
    }

    /**
     * Get all excluded route names
     *
     * @return string[]
     */
    public function getExcludedRouteNames(): array
    {
        $excludedRoutes = array_merge(
            self::DEFAULT_EXCLUDED_ROUTES,
            $this->additionalExcludedRouteNames,
        );

        // Remove duplicates and re-index
        return array_values(array_unique($excludedRoutes));
    }
}
