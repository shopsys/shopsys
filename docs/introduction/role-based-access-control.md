# Role-Based Access Control (RBAC)

[TOC]

Shopsys Platform implements a comprehensive role-based access control (RBAC) system that provides unified security across both the Administration interface and Frontend API. This system ensures that users can only access the functionality and data appropriate to their assigned roles.

## Overview

The RBAC system is built on top of Symfony's security component and provides:

- **Unified role management** across Admin and Frontend API contexts
- **Fine-grained permissions** for different levels of access
- **Flexible role providers** for organizing and extending roles
- **Context-based role organization** to separate different application areas
- **Event-driven architecture** for cross-bundle integration

## Core Concepts

### Roles

Roles define what a user can do in the system. Each role has a unique constant and can be assigned different permission levels:

```php
// Admin roles
ROLE_PRODUCT           // Product management
ROLE_ORDER             // Order management
ROLE_ADMINISTRATOR     // User administration

// System roles
ROLE_SUPER_ADMIN       // Maximum privileges
ROLE_ADMIN             // Administrative access

// Frontend API roles
ROLE_API_ALL                      // All API privileges
ROLE_API_CUSTOMER_SELF_MANAGE     // Self-management only
ROLE_API_CUSTOMER_SEES_PRICES     // Price visibility
```

### Permissions

Permissions define the level of access a role provides:

```php
Permission::VIEW     // Read-only access
Permission::EDIT     // Modify existing entities
Permission::CREATE   // Create new entities
Permission::DELETE   // Delete entities
Permission::FULL     // All permissions combined
```

### Role Naming Convention

The system automatically generates role constants by combining the base role name with permission levels using the pattern `ROLE_BASENAME_PERMISSION`:

```php
// Base role: ROLE_PRODUCT
// Generated role constants:
ROLE_PRODUCT_VIEW     // ROLE_PRODUCT + Permission::VIEW
ROLE_PRODUCT_EDIT     // ROLE_PRODUCT + Permission::EDIT
ROLE_PRODUCT_CREATE   // ROLE_PRODUCT + Permission::CREATE
ROLE_PRODUCT_DELETE   // ROLE_PRODUCT + Permission::DELETE
ROLE_PRODUCT_FULL     // ROLE_PRODUCT + Permission::FULL

// Base role: ROLE_ORDER
ROLE_ORDER_VIEW       // ROLE_ORDER + Permission::VIEW
ROLE_ORDER_EDIT       // ROLE_ORDER + Permission::EDIT
ROLE_ORDER_CREATE     // ROLE_ORDER + Permission::CREATE
ROLE_ORDER_DELETE     // ROLE_ORDER + Permission::DELETE
ROLE_ORDER_FULL       // ROLE_ORDER + Permission::FULL
```

When you define a role with multiple permissions, the system automatically creates individual role constants for each permission level, allowing for fine-grained access control.

!!! note

    Use `RoleIdentifierHelper` class for programmatic role manipulation - generating role constants, extracting base roles, and parsing permissions from role constants.

### Contexts

The system uses contexts to organize roles by their intended application area, ensuring that roles from different parts of the system don't interfere with each other:

- **AdminContext** - Administration interface roles
- **FrontendApiContext** - Frontend API roles
- **Custom contexts** - Project-specific role organization

!!! info "Context System"

    For detailed information about how contexts work in Shopsys Platform, see [Context System](context-system.md).

## Role Hierarchy

The system automatically manages role inheritance:

```
ROLE_SUPER_ADMIN (highest)
    └── ROLE_ALL (all FULL permissions)
        ├── ROLE_PRODUCT_FULL
        │   ├── ROLE_PRODUCT_EDIT (includes VIEW)
        │   ├── ROLE_PRODUCT_CREATE (includes VIEW)
        │   └── ROLE_PRODUCT_DELETE (includes VIEW)
        └── [other role hierarchies...]
ROLE_ADMIN (lowest) - allows login and access to basic admin functions
```

!!! note

    Permission dependencies: FULL permission includes EDIT, CREATE, and DELETE. Each of these permissions automatically includes VIEW permission (EDIT, CREATE, DELETE all inherit VIEW).

## Role Provider System

Roles are organized using a provider system that allows for modular role management. Each provider is tied to a specific context, ensuring that roles from different application areas don't mix.

### Core Providers

- **CoreAdminRoleProvider** - Standard administration roles (AdminContext)
- **CoreFrontendApiRoleProvider** - Frontend API roles (FrontendApiContext)

### Project Providers

- **AdminRoleProvider** - Ready-to-use provider for adding custom admin roles (AdminContext)

### Creating Custom Roles

You can extend the system with custom roles by implementing `RoleProviderInterface`. Each provider must specify which context it targets:

```php
namespace App\Model\Security;

use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

class CustomRoleProvider implements RoleProviderInterface
{
    public function getRoles(): array
    {
        return [
            new Role('ROLE_MARKETING', t('Marketing'), [Permission::FULL]),
            new Role('ROLE_WAREHOUSE', t('Warehouse'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_REPORT', t('Reports')), // Single role (no permissions)
        ];
    }

    public function getTargetContext(): string
    {
        return AdminContext::class; // These roles are for admin context only
    }
}
```

## Access Control Implementation

### Administration Interface

The admin interface uses PHP attributes for route protection. For detailed information about available attributes and implementation patterns, see [Admin Rights and Access Control](../administration/admin-rights.md).

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;

#[ForRole('ROLE_PRODUCT')]
class ProductController extends AdminBaseController
{
    #[Route('/product/list')]
    #[CanView]
    public function listAction(): Response { }
}
```

### Frontend API

The Frontend API uses GraphQL access directives:

```yaml
ProductList:
    type: '[Product!]!'
    description: 'Get list of products'
    access: "@=isGranted('ROLE_API_CUSTOMER_SEES_PRICES')"
    resolve: "@=query('productListQuery', args)"
```

## Management Tools

### Role Exploration

!!! note

    Use `php bin/console shopsys:rbac:roles` to explore available roles and their usage. Filter by role name like `product` or use `--context AdminContext` for specific contexts. Shows detailed route information when ≤5 roles are found.

### Access Control Checking

The platform provides the `AccessChecker` service for programmatic access control validation. This service integrates with the role system to determine whether users have appropriate permissions for specific operations.

You can also use Symfony's standard `AuthorizationCheckerInterface` for basic role and permission checking, which remains fully compatible with the RBAC system.

### Voters

For complex access control logic, the system supports Symfony voters for custom authorization decisions.

## How to Display Roles in Administration

The platform provides the [RolesType](using-form-types.md#rolestype) form type for displaying and managing roles in administration forms. This form type renders an interactive grid of roles with permission checkboxes, making it easy for administrators to manage user permissions.

### Organizing Roles into Sections

When the `grouped` option is enabled (default), roles are organized into sections to improve usability and navigation. This is particularly useful when you have many roles in your system.

#### Creating a Role Section Provider

To organize roles into sections, create a custom role section provider:

```php
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;

class AdminRoleSectionsProvider extends AbstractRoleSectionProvider
{
    public const string ORDERS_CUSTOMERS = 'orders_customers';
    public const string PRODUCTS_CATALOG = 'products_catalog';
    public const string MARKETING_PROMOTIONS = 'marketing_promotions';

    protected function defineSections(): void
    {
        // Add sections with identifier, translatable name, priority (lower = first), and optional icon
        $this->addSection(new RoleSection(self::ORDERS_CUSTOMERS, t('Orders & Customers'), 20, 'cart'));
        $this->addSection(new RoleSection(self::PRODUCTS_CATALOG, t('Products & Catalog'), 30, 'tag'));
        $this->addSection(new RoleSection(self::MARKETING_PROMOTIONS, t('Marketing & Promotions'), 40, 'light-bulb'));
    }

    public static function getTargetContext(): string
    {
        return AdminContext::class; // This provider handles admin context roles
    }
}
```

#### Assigning Roles to Sections

When defining roles, assign them to appropriate sections:

```php
use Shopsys\FrameworkBundle\Component\Security\Role\Role;

class CustomRoleProvider implements RoleProviderInterface
{
    public function getRoles(): array
    {
        return [
            new Role(
                'ROLE_ORDER',
                t('Order management'),
                AdminRoleSectionsProvider::ORDERS_CUSTOMERS, // Assign to section
                [Permission::FULL]
            ),
            new Role(
                'ROLE_PRODUCT',
                t('Product management'),
                AdminRoleSectionsProvider::PRODUCTS_CATALOG,
                [Permission::VIEW, Permission::EDIT]
            ),
        ];
    }
}
```

Roles without an assigned section are automatically placed in the "Other" section with the lowest priority.

## Security Best Practices

1. **Principle of least privilege** - Grant only the minimum permissions needed
2. **Regular audits** - Use console commands to review role assignments and coverage
3. **Test thoroughly** - Validate access control in both positive and negative scenarios
4. **Document custom roles** - Maintain clear documentation for project-specific roles
5. **Monitor coverage** - Use CI/CD integration to ensure all routes are properly protected

## Related Documentation

- [Admin Rights and Access Control](../administration/admin-rights.md) - Attributes and menu integration
- [Frontend API Access Control](../cookbook/manage-access-control-frontend-api.md) - API-specific implementation
- [Context System](context-system.md) - Understanding contexts in Shopsys
