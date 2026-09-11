<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Attribute;

use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Shopsys\FrameworkBundle\Component\Security\Attribute\AbstractCanPermissionAttribute;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

/**
 * Converts the security attributes of a controller method to access control rules.
 * Has no dependencies, so the rules can be resolved anywhere, including the container build.
 * The attributes are looked up along the inheritance chain, so an overriding method (or a subclass) without attributes
 * keeps the attributes of the parent declaration.
 */
abstract class AbstractAttributeProcessor
{
    /**
     * Resolves the rules in the order of their priority: SuperAdminOnly (class or method) wins over everything,
     * then RequireRole, RequirePermission and the Can* attributes are combined, PublicAccess applies only when nothing else does
     *
     * @param string|null $classRole role used by the Can* attributes without an explicit role
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    protected function collectRules(ReflectionClass $class, ReflectionMethod $method, ?string $classRole): array
    {
        if (ReflectionHelper::getClassAttribute($class, SuperAdminOnly::class) !== null) {
            return [new AccessControlRuleData(SystemRole::SUPER_ADMIN)];
        }

        $superAdminOnly = ReflectionHelper::getMethodAttribute($method, SuperAdminOnly::class);

        if ($superAdminOnly !== null) {
            return [new AccessControlRuleData(SystemRole::SUPER_ADMIN, $superAdminOnly->getMethods())];
        }

        $rules = [
            ...$this->createRequireRoleRules($method),
            ...$this->createRequirePermissionRules($method),
            ...$this->createPermissionRules($method, $classRole),
        ];

        if ($rules !== []) {
            return $rules;
        }

        return $this->createPublicAccessRules($class, $method);
    }

    /**
     * Returns the role declared by the ForRole attribute of the class
     */
    protected function getClassRole(ReflectionClass $class): ?string
    {
        return ReflectionHelper::getClassAttribute($class, ForRole::class)?->role;
    }

    /**
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    private function createRequireRoleRules(ReflectionMethod $method): array
    {
        $rules = [];

        foreach (ReflectionHelper::getMethodAttributes($method, RequireRole::class) as $requireRole) {
            foreach ($requireRole->roles as $role) {
                $rules[] = new AccessControlRuleData($role, $requireRole->getMethods());
            }
        }

        return $rules;
    }

    /**
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    private function createRequirePermissionRules(ReflectionMethod $method): array
    {
        $rules = [];

        foreach (ReflectionHelper::getMethodAttributes($method, RequirePermission::class) as $requirePermission) {
            $rules[] = new AccessControlRuleData(
                RoleIdentifierHelper::getIdentifierWithPermission($requirePermission->role, $requirePermission->permission),
                $requirePermission->getMethods(),
            );
        }

        return $rules;
    }

    /**
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    private function createPermissionRules(ReflectionMethod $method, ?string $classRole): array
    {
        $rules = [];

        foreach (ReflectionHelper::getMethodAttributes($method, AbstractCanPermissionAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $permissionAttribute) {
            $role = $permissionAttribute->getRole() ?? $classRole;

            if ($role === null) {
                throw new InvalidArgumentException(
                    sprintf('Role must be specified either in %s attribute or class-level ForRole attribute', $permissionAttribute::class),
                );
            }

            $rules[] = new AccessControlRuleData(
                RoleIdentifierHelper::getIdentifierWithPermission($role, $permissionAttribute->getPermission()),
                $permissionAttribute->getMethods(),
            );
        }

        return $rules;
    }

    /**
     * Method-level PublicAccess takes precedence over the class-level one
     *
     * @return list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData>
     */
    private function createPublicAccessRules(ReflectionClass $class, ReflectionMethod $method): array
    {
        $publicAccess = ReflectionHelper::getMethodAttribute($method, PublicAccess::class);

        if ($publicAccess !== null) {
            return [new AccessControlRuleData(SystemRole::PUBLIC_ACCESS, $publicAccess->getMethods())];
        }

        if (ReflectionHelper::getClassAttribute($class, PublicAccess::class) !== null) {
            return [new AccessControlRuleData(SystemRole::PUBLIC_ACCESS)];
        }

        return [];
    }
}
