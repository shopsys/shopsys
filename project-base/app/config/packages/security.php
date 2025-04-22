<?php

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
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

    $accessControlRules = Roles::getAccessControlRules();

    foreach ($accessControlRules as $rule) {
        $accessControl = $security->accessControl();
        $accessControl
            ->path($rule->path)
            ->roles($rule->roles);

        if ($rule->methods !== []) {
            $accessControl->methods($rule->methods);
        }
        if ($rule->host) {
            $accessControl->host($rule->host);
        }
        if ($rule->ips) {
            $accessControl->ips($rule->ips);
        }
        if ($rule->port) {
            $accessControl->port($rule->port);
        }
        if ($rule->requestMatcher) {
            $accessControl->requestMatcher($rule->requestMatcher);
        }
        if ($rule->routeName) {
            $accessControl->route($rule->routeName);
        }
        if ($rule->allowIf) {
            $accessControl->allowIf($rule->allowIf);
        }
        if ($rule->requiresChannel) {
            $accessControl->requiresChannel($rule->requiresChannel);
        }

        foreach ($rule->attributes as $attribute => $value) {
            $accessControl->attribute($attribute, $value);
        }
    }
};
