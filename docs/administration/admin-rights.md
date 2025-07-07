# Admin Rights and Role-Based Access Control

The administration uses a flexible role-based access control (RBAC) system built on top of Symfony's security component. This system provides fine-grained permissions for different administrative functions using intuitive PHP attributes.

## Overview

The access control system consists of several key components:

1. **Roles** - Define what a user can do (e.g., `ROLE_PRODUCT`, `ROLE_ORDER`)
2. **Permissions** - Define the level of access (VIEW, EDIT, CREATE, DELETE, FULL)
3. **Security Attributes** - Connect routes to required roles using clear, intention-revealing attributes
4. **Role Hierarchy** - Automatically grants subordinate permissions

## Permission System

Each role can have different permission levels defined in the `Permission` enum:

```php
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

Permission::VIEW    // Read-only access
Permission::EDIT    // Modify existing entities
Permission::CREATE  // Create new entities
Permission::DELETE  // Delete entities
Permission::FULL    // All permissions (includes VIEW, EDIT, CREATE, DELETE)
```

## Available Security Attributes

The system provides multiple types of security attributes for different use cases:

### CRUD Attributes (Recommended)

- `#[CanView]` / `#[CanView('ROLE_NAME')]` - Requires VIEW permission
- `#[CanEdit]` / `#[CanEdit('ROLE_NAME')]` - Requires EDIT permission
- `#[CanCreate]` / `#[CanCreate('ROLE_NAME')]` - Requires CREATE permission
- `#[CanDelete]` / `#[CanDelete('ROLE_NAME')]` - Requires DELETE permission

### Administrative Attributes

- `#[RequireRole('ROLE_NAME')]` - Requires specific role(s)
- `#[RequirePermission('ROLE_NAME', Permission::TYPE)]` - Requires specific permission
- `#[SuperAdminOnly]` - Super admin access only
- `#[PublicAccess]` - No authentication required

### Class-Level Attributes

- `#[ForRole('ROLE_NAME')]` - Sets default role for all CRUD attributes in the controller
- `#[SuperAdminOnly]` - Restricts all actions in the controller to super admin only (highest priority)
- `#[PublicAccess]` - Makes all actions in the controller publicly accessible (can be overridden by method-level attributes)

### Usage Examples

The most common and intuitive way to secure admin actions:

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;

// Clean approach - set default role for the entire controller
#[ForRole('ROLE_PRODUCT')]
class ProductController extends AdminBaseController
{
    #[Route(path: '/product/list')]
    #[CanView]
    public function listAction(): Response { }

    #[Route(path: '/product/edit/{id}')]
    #[CanEdit]
    public function editAction(int $id): Response { }

    #[Route(path: '/product/new')]
    #[CanCreate]
    public function newAction(): Response { }

    #[Route(path: '/product/delete/{id}')]
    #[CanDelete]
    public function deleteAction(int $id): Response { }
}

// Traditional explicit approach (also supported)
class ProductController extends AdminBaseController
{
    #[Route(path: '/product/list')]
    #[CanView('ROLE_PRODUCT')]
    public function listAction(): Response { }

    #[Route(path: '/product/edit/{id}')]
    #[CanEdit('ROLE_PRODUCT')]
    public function editAction(int $id): Response { }
}
```

### Method-Specific Permissions

Different permissions for different HTTP methods:

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Symfony\Component\Routing\Attribute\Route;

// With ForRole at class level
#[Route(path: '/product/edit/{id}')]
#[CanView(methods: [HttpMethod::GET])]    // Show form
#[CanEdit(methods: ['POST'])]   // Process form (you can also use string instead of HttpMethod enum)
public function editAction(Request $request, int $id): Response { }

// Explicit approach
#[Route(path: '/product/edit/{id}')]
#[CanView('ROLE_PRODUCT', [HttpMethod::GET])]    // Show form
#[CanEdit('ROLE_PRODUCT', ['POST'])]   // Process form
public function editAction(Request $request, int $id): Response { }
```

### Administrative Access

For system administration and user management:

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

#[Route(path: '/administrator/list')]
#[RequireRole(SystemRole::ADMIN)]
public function listAdministratorsAction(): Response { }

#[Route(path: '/system/dangerous-operation')]
#[SuperAdminOnly]
public function dangerousOperationAction(): Response { }
```

### Public Access

For endpoints that don't require authentication:

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;

#[Route(path: '/health')]
#[PublicAccess]
public function healthCheckAction(): Response { }
```

### Advanced Permissions

For complex scenarios requiring multiple roles or specific permissions:

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

#[Route(path: '/report/cross-system')]
#[RequirePermission('ROLE_ORDER', Permission::VIEW)]
#[RequirePermission('ROLE_PRODUCT', Permission::VIEW)]
public function crossSystemReportAction(): Response { }
```

## Attribute Priority System

When multiple security attributes are applied to the same method, they follow a specific priority order:

### 1. Class-Level vs Method-Level

- **SuperAdminOnly at class-level** takes precedence over all method-level attributes
- **PublicAccess at class-level** can be overridden by method-level security attributes
- **ForRole** provides default role for CRUD attributes but can be overridden per method

### 2. Access Type Priority

1. **SuperAdminOnly** - Highest priority, restricts to super admin only
2. **Other attributes** - Processed in combination (all must be satisfied)
3. **PublicAccess** - Lowest priority, only applies if no other security attributes are present

### 3. Combination Examples

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Symfony\Component\Routing\Attribute\Route;


// Class-level SuperAdminOnly overrides method-level CanView
#[SuperAdminOnly]
class SystemController extends AdminBaseController
{
    #[Route('/system/status')]
    #[CanView(SystemRole::ADMIN)] // This is ignored - SuperAdminOnly takes precedence
    public function statusAction(): Response { }
}

// Class-level PublicAccess can be overridden per method
#[PublicAccess]
class ApiController extends AdminBaseController
{
    #[Route('/api/health')]
    public function healthAction(): Response { } // Uses class-level PublicAccess

    #[Route('/api/secure-endpoint')]
    #[CanView('ROLE_API')] // Overrides class-level PublicAccess - requires authentication
    public function secureEndpointAction(): Response { }

    #[Route('/api/admin-only')]
    #[SuperAdminOnly] // Overrides class-level PublicAccess - super admin only
    public function adminOnlyAction(): Response { }
}

// ForRole provides default, can be overridden per method
#[ForRole('ROLE_PRODUCT')]
class ProductController extends AdminBaseController
{
    #[Route('/product/list')]
    #[CanView] // Uses ROLE_PRODUCT from ForRole
    public function listAction(): Response { }

    #[Route('/product/admin-only')]
    #[RequireRole(SystemRole::ADMIN)] // Overrides ForRole default
    public function adminOnlyAction(): Response { }
}

// Multiple requirements - ALL must be satisfied
#[Route('/report/complex')]
#[CanView('ROLE_PRODUCT')]                    // Must have product view
#[RequirePermission('ROLE_ORDER', Permission::VIEW)] // AND order view
#[RequireRole('ROLE_MANAGER')]                // AND manager role
public function complexReportAction(): Response { }
```

### 4. Method-Specific HTTP Methods

```php
#[Route('/product/edit/{id}')]
#[CanView(methods: ['GET'])]     // GET requires VIEW
#[CanEdit(methods: ['POST'])]    // POST requires EDIT
#[SuperAdminOnly(methods: ['DELETE'])] // DELETE requires super admin
public function editAction(Request $request): Response { }
```

## Role Hierarchy

The system automatically manages role inheritance:

1. **FULL permission** includes VIEW, EDIT, CREATE, and DELETE
2. **ROLE_SUPER_ADMIN** has access to everything
3. **ROLE_ADMIN** has basic administrative access
4. **ROLE_ALL** combines all FULL permissions
5. **ROLE_ALL_VIEW** combines all VIEW permissions

The role hierarchy follows the pattern `ROLE_BASENAME_PERMISSION`:

```
ROLE_SUPER_ADMIN
    └── ROLE_ADMIN
        └── ROLE_ALL (all FULL permissions)
            ├── ROLE_PRODUCT_FULL
            │   ├── ROLE_PRODUCT_VIEW
            │   ├── ROLE_PRODUCT_EDIT
            │   ├── ROLE_PRODUCT_CREATE
            │   └── ROLE_PRODUCT_DELETE
            └── ROLE_ORDER_FULL
                ├── ROLE_ORDER_VIEW
                ├── ROLE_ORDER_EDIT
                ├── ROLE_ORDER_CREATE
                └── ROLE_ORDER_DELETE
```

## Role Provider System

The platform uses a role provider system to organize and manage roles. Each provider can define related roles, and the system automatically validates for duplicates.

### Default Roles

Default platform roles are provided by `CoreAdminRoleProvider` and include standard entities:

- `ROLE_PRODUCT` - Product management
- `ROLE_ORDER` - Order management
- `ROLE_CUSTOMER` - Customer management
- `ROLE_ADMINISTRATOR` - Administrator management
- And many more...

### Creating Custom Roles

To add new roles to your project, you have two options:

#### Option 1: Use the Prepared AppRoleProvider

The project comes with a ready-to-use `AppRoleProvider` class at `src/Model/Security/AppRoleProvider.php`. Simply add your roles there:

```php
namespace App\Model\Security;

use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

class AppRoleProvider implements RoleProviderInterface
{
    public function getRoles(): array
    {
        return [
            new Role('ROLE_MARKETING', t('Marketing'), [Permission::FULL]),
            new Role('ROLE_WAREHOUSE', t('Warehouse'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_REPORT', t('Reports')), // If Permissions are not specified, role is considered as System role and this role is hidden from USER
        ];
    }
}
```

#### Option 2: Create a New Role Provider

If you want to organize roles separately (e.g., by module), create a new provider:

```php
namespace App\Model\Security;

use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

class MarketingRoleProvider implements RoleProviderInterface
{
    public function getRoles(): array
    {
        return [
            new Role('ROLE_CAMPAIGN', t('Campaigns'), [Permission::FULL]),
            new Role('ROLE_ANALYTICS', t('Analytics'), [Permission::VIEW]),
        ];
    }
}
```

#### Using Custom Roles in Controllers

```php
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole('ROLE_MARKETING')]
class MarketingController extends AdminBaseController
{
    #[Route(path: '/marketing/campaigns')]
    #[CanView]
    public function campaignsAction(): Response { }
}
```

## Menu Integration

The admin menu automatically respects access control rules. Menu items are only shown if the user has access to the linked route. This is handled by `MenuItemsGrantedRolesSubscriber`.

## Best Practices

1. **Use intention-revealing attributes** - `#[CanView('ROLE_PRODUCT')]` is clearer than complex configurations
2. **Use appropriate permissions** - Don't grant EDIT when only VIEW is needed
3. **Test thoroughly** - Use the console command to verify coverage
4. **Document custom roles** - Keep track of any project-specific roles
5. **Follow naming conventions** - Use `ROLE_` prefix for all roles
