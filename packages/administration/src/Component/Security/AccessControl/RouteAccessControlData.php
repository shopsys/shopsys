<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use ReflectionClass;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Webmozart\Assert\Assert;

/**
 * Value object representing access control data for a route
 * Contains all access control rules for a single route
 * This class is used internally by the access control system and should not be used directly
 *
 * @internal
 */
class RouteAccessControlData
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRule[] $accessControlRules
     */
    public function __construct(
        public readonly ?string $routeName,
        public readonly array $accessControlRules,
        public readonly string $controllerClass,
        public readonly string $controllerMethod,
    ) {
        Assert::allIsInstanceOf($accessControlRules, AccessControlRule::class);
    }

    public function hasAnyRules(): bool
    {
        return $this->accessControlRules !== [];
    }

    /**
     * Check if route has access with specific HTTP method and roles check callback
     *
     * @param \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod $httpMethod The HTTP method to check
     * @param callable(string): bool $hasRoleCallback Callback to check if user has specific role
     * @return bool True if access is granted, false otherwise
     */
    public function hasAccess(HttpMethod $httpMethod, callable $hasRoleCallback): bool
    {
        if ($this->hasAnyRules() === false) {
            return false;
        }

        $hasApplicableRules = false;

        foreach ($this->accessControlRules as $rule) {
            // Check if HTTP method restriction applies (empty methods array means all methods are allowed)
            if (!$rule->appliesToMethod($httpMethod)) {
                continue;
            }

            $hasApplicableRules = true;

            // Check if user has the required role for this rule - ALL applicable rules must pass
            if (!$rule->hasAccess($httpMethod, $hasRoleCallback)) {
                return false; // Access denied if any applicable rule fails
            }
        }

        // If no rules were applicable, deny access (method not allowed)
        return $hasApplicableRules;
    }

    /**
     * Format controller info as "ShortClassName::methodName"
     */
    public function formatControllerInfo(): string
    {
        // Handle cases where controller class doesn't exist (UnknownController, etc.)
        if (!class_exists($this->controllerClass)) {
            // Extract just the class name from the full path
            $parts = explode('\\', $this->controllerClass);
            $shortControllerClass = array_last($parts);
        } else {
            $shortControllerClass = (new ReflectionClass($this->controllerClass))->getShortName();
        }

        return "{$shortControllerClass}::{$this->controllerMethod}";
    }
}
