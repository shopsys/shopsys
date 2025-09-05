<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Attribute;

use InvalidArgumentException;
use ReflectionMethod;
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
    /**
     * @param \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleFactory $accessControlRuleFactory
     */
    public function __construct(
        private readonly AccessControlRuleFactory $accessControlRuleFactory,
    ) {
    }

    /**
     * @param \ReflectionMethod $method
     * @return \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRule[]
     */
    public function processMethod(ReflectionMethod $method): array
    {
        $rules = [];

        // Get class-level role if ForRole attribute is present
        $classRole = $this->getClassRole($method);

        // Check if class has SuperAdminOnly attribute
        $classSuperAdminOnly = $this->getClassSuperAdminOnly($method);

        // Check if class has PublicAccess attribute
        $classPublicAccess = $this->getClassPublicAccess($method);

        // Process SuperAdminOnly first (highest priority - most restrictive)
        if ($classSuperAdminOnly) {
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
            } elseif ($classPublicAccess) {
                // Fall back to class-level PublicAccess only if no method-level attributes exist
                $rules[] = $this->accessControlRuleFactory->create(SystemRole::PUBLIC_ACCESS);
            }
        }

        return $rules;
    }

    /**
     * @param \ReflectionMethod $method
     * @param \Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRule[] $rules
     * @param class-string<\Shopsys\FrameworkBundle\Component\Security\Attribute\PermissionAttributeInterface> $attributeClass
     * @param string|null $classRole
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
     * @param \ReflectionMethod $method
     * @return string|null
     */
    private function getClassRole(ReflectionMethod $method): ?string
    {
        $classAttributes = $method->getDeclaringClass()->getAttributes(ForRole::class);

        if ($classAttributes === []) {
            return null;
        }

        return $classAttributes[0]->newInstance()->role;
    }

    /**
     * @param \ReflectionMethod $method
     * @return bool
     */
    private function getClassSuperAdminOnly(ReflectionMethod $method): bool
    {
        $classAttributes = $method->getDeclaringClass()->getAttributes(SuperAdminOnly::class);

        return $classAttributes !== [];
    }

    /**
     * @param \ReflectionMethod $method
     * @return bool
     */
    private function getClassPublicAccess(ReflectionMethod $method): bool
    {
        $classAttributes = $method->getDeclaringClass()->getAttributes(PublicAccess::class);

        return $classAttributes !== [];
    }
}
