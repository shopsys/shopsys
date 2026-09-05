<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Attribute;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\Crud\CrudRoleConstantProvider;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleIdentifierHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

/**
 * Processes security attributes and converts them to AccessControlRules
 */
final class AttributeProcessor
{
    public function __construct(
        private readonly AccessControlRuleFactory $accessControlRuleFactory,
        private readonly CrudRoleConstantProvider $crudRoleConstantProvider,
    ) {
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRule[]
     */
    public function processMethod(ReflectionClass $class, ReflectionMethod $method): array
    {
        $rules = [];

        // Process SuperAdminOnly first (highest priority - most restrictive)
        if ($this->getClassAttribute($class, SuperAdminOnly::class) !== null) {
            $rules[] = $this->accessControlRuleFactory->create(SystemRole::SUPER_ADMIN);

            // If SuperAdminOnly is present at class level, don't process other attributes for security
            return $rules;
        }

        $superAdminOnly = $method->getAttributes(SuperAdminOnly::class);

        if ($superAdminOnly !== []) {
            $attribute = $superAdminOnly[0]->newInstance();
            $rules[] = $this->accessControlRuleFactory->create(SystemRole::SUPER_ADMIN, $attribute->getMethods());

            // If SuperAdminOnly is present at method level, don't process other attributes for security
            return $rules;
        }

        // Process RequireRole
        $requireRole = $method->getAttributes(RequireRole::class);

        foreach ($requireRole as $attr) {
            $attribute = $attr->newInstance();

            foreach ($attribute->roles as $role) {
                $rules[] = $this->accessControlRuleFactory->create($role, $attribute->getMethods());
            }
        }

        // Process RequirePermission
        $requirePermission = $method->getAttributes(RequirePermission::class);

        foreach ($requirePermission as $attr) {
            $attribute = $attr->newInstance();
            $roleWithPermission = RoleIdentifierHelper::getIdentifierWithPermission($attribute->role, $attribute->permission);
            $rules[] = $this->accessControlRuleFactory->create($roleWithPermission, $attribute->getMethods());
        }

        // CRUD controllers use their role constant (generated, or set by ForRole — resolved at compile time including extension overrides) so custom routes are guarded by the same role as the built-in actions, other classes read the ForRole attribute directly
        $classRole = $this->crudRoleConstantProvider->findRoleConstant($class->getName())
            ?? $this->getClassAttribute($class, ForRole::class)?->role;

        // Process CRUD attributes
        $this->processPermissionAttributes($method, $rules, CanView::class, $classRole);
        $this->processPermissionAttributes($method, $rules, CanEdit::class, $classRole);
        $this->processPermissionAttributes($method, $rules, CanCreate::class, $classRole);
        $this->processPermissionAttributes($method, $rules, CanDelete::class, $classRole);

        // Process PublicAccess last (lowest priority - only if no other rules exist)
        if ($rules === []) {
            // Check for method-level PublicAccess first
            $publicAccess = $method->getAttributes(PublicAccess::class);

            if ($publicAccess !== []) {
                $attribute = $publicAccess[0]->newInstance();
                $rules[] = $this->accessControlRuleFactory->create(SystemRole::PUBLIC_ACCESS, $attribute->getMethods());
            } elseif ($this->getClassAttribute($class, PublicAccess::class) !== null) {
                // Fall back to class-level PublicAccess only if no method-level attributes exist
                $rules[] = $this->accessControlRuleFactory->create(SystemRole::PUBLIC_ACCESS);
            }
        }

        return $rules;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRule[] $rules
     * @param class-string<\Shopsys\FrameworkBundle\Component\Security\Attribute\PermissionAttributeInterface> $attributeClass
     */
    private function processPermissionAttributes(
        ReflectionMethod $method,
        array &$rules,
        string $attributeClass,
        ?string $classRole,
    ): void {
        $attributes = $method->getAttributes($attributeClass);

        foreach ($attributes as $attr) {
            /** @var \Shopsys\FrameworkBundle\Component\Security\Attribute\PermissionAttributeInterface $attribute */
            $attribute = $attr->newInstance();
            $role = $attribute->getRole() ?? $classRole;

            if ($role === null) {
                throw new InvalidArgumentException(
                    sprintf('Role must be specified either in %s attribute or class-level ForRole attribute', $attributeClass),
                );
            }

            $roleWithPermission = RoleIdentifierHelper::getIdentifierWithPermission($role, $attribute->getPermission());
            $rules[] = $this->accessControlRuleFactory->create($roleWithPermission, $attribute->getMethods());
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function getClassAttribute(ReflectionClass $class, string $attributeClass): ?object
    {
        $attributes = $class->getAttributes($attributeClass);

        if ($attributes !== []) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
