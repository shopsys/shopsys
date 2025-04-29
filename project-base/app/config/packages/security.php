<?php

use App\Environment;
use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRulesAttributeFinder;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlData;
use Symfony\Config\SecurityConfig;

/**
 * If your IDE does not recognize the SecurityConfig class, be sure to mark the "/var/cache/dev/Symfony" folder as not excluded
 * @see https://symfony.com/doc/current/configuration.html#using-php-configbuilders
 */
return static function (SecurityConfig $security): void {
    $roleHierarchy = [...Roles::getRolesHierarchy(), ...CustomerUserRole::getRolesHierarchy()];

    foreach ($roleHierarchy as $role => $inheritedRoles) {
        $security->roleHierarchy($role, $inheritedRoles);
    }

    $staticAccessControlRules = [
        // This makes the logout route accessible during two-factor authentication. Allows the user to cancel two-factor authentication if they need to.
        '^/%admin_url%/logout' => ['PUBLIC_ACCESS'],
        // This ensures that the form can only be accessed when two-factor authentication is in progress.
        '^/%admin_url%/2fa' => ['IS_AUTHENTICATED_2FA_IN_PROGRESS'],
        '^/%admin_url%/$' => ['PUBLIC_ACCESS'],
        '^/efconnect' => [Roles::ROLE_ADMIN],
        '^/elfinder' => [Roles::ROLE_ADMIN],
    ];

    foreach ($staticAccessControlRules as $path => $roles) {
        $accessControlConfig = $security->accessControl();
        $accessControlConfig
            ->path($path)
            ->roles($roles);
    }

    foreach (getRouteAccessControlRules() as $routeAccessControlRule) {
        $accessControlRule = $routeAccessControlRule->accessControlRule;
        $accessControlConfig = $security->accessControl();
        $accessControlConfig
            ->route($routeAccessControlRule->routeName)
            ->roles($accessControlRule->roles);

        if ($accessControlRule->methods !== []) {
            $accessControlConfig->methods($accessControlRule->methods);
        }
        if ($accessControlRule->host) {
            $accessControlConfig->host($accessControlRule->host);
        }
        if ($accessControlRule->ips) {
            $accessControlConfig->ips($accessControlRule->ips);
        }
        if ($accessControlRule->port) {
            $accessControlConfig->port($accessControlRule->port);
        }
        if ($accessControlRule->requestMatcher) {
            $accessControlConfig->requestMatcher($accessControlRule->requestMatcher);
        }
        if ($accessControlRule->allowIf) {
            $accessControlConfig->allowIf($accessControlRule->allowIf);
        }
        if ($accessControlRule->requiresChannel) {
            $accessControlConfig->requiresChannel($accessControlRule->requiresChannel);
        }

        foreach ($accessControlRule->attributes as $attribute => $value) {
            $accessControlConfig->attribute($attribute, $value);
        }
    }
};

/**
 * @return \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlData[]
 */
function getRouteAccessControlRules(): array
{
    $accessControlCacheFile = sprintf(__DIR__ . '/../../var/cache/%s/access_control_rules.json', Environment::getEnvironment());

    if (file_exists($accessControlCacheFile)) {
        $accessControlRulesArray = json_decode(file_get_contents($accessControlCacheFile), true, 512, JSON_THROW_ON_ERROR);
        $routeAccessControlRules = [];
        foreach ($accessControlRulesArray as $accessControlRuleArray) {
            $routeAccessControlRules[] = RouteAccessControlData::fromJson($accessControlRuleArray);
        }
    } else {
        $accessControlRulesAttributeFinder = new AccessControlRulesAttributeFinder();

        $appControllerDirectory = __DIR__ . '/../../src/Controller/Admin';
        $routeAccessControlRules = $accessControlRulesAttributeFinder->findAll([$appControllerDirectory, getFrontendApiControllersDirectory()]);

        file_put_contents($accessControlCacheFile, json_encode($routeAccessControlRules, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    return $routeAccessControlRules;
}

/**
 * @return string
 */
function getFrontendApiControllersDirectory(): string
{
    if (file_exists(__DIR__ . '/../../../../parameters_monorepo.yaml')) {
        return __DIR__ . '/../../../../packages/frontend-api/src/Controller/';
    }

    return __DIR__ . '/../../vendor/shopsys/frontend-api/src/Controller/';
}
