# Admin Rights and Access Control

The administration interface uses PHP attributes to protect routes and integrate with the platform's role-based access control system. This provides intuitive, declaration-based security that's easy to read and maintain.

!!! info "Global RBAC System"

    This page focuses on administration-specific implementation. For a comprehensive overview of the role-based access control system used across the platform, see [Role-Based Access Control](../introduction/role-based-access-control.md).

## How Access Control Works

The administration interface uses an automatic access control system that secures all admin routes based on PHP attributes. The system scans your controllers and automatically enforces security rules without requiring manual configuration for each route.

### Automatic Route Scanning

The system automatically discovers and secures admin routes by:

- **Route Discovery**: All routes with names starting with `admin_` or paths starting with `/admin/` are automatically identified as admin routes
- **Attribute Scanning**: Each admin controller class and method is scanned for security attributes (like `#[CanView]`, `#[RequireRole]`, etc.)
- **Rule Generation**: Security attributes are converted into access control rules and cached for performance
- **Automatic Enforcement**: Every admin request is automatically checked against these rules before controller execution

This means that simply adding security attributes to your controller methods automatically secures them - no additional configuration is needed.

!!! info "Access Control coverage"

    The access control coverage of all the controller actions is checked automatically by the `access-control-rules-check` [phing](../introduction/console-commands-for-application-management-phing-targets.md) target (it is a part of `standards` check)

    All routes must be covered by at least one security attribute; otherwise, the target will fail. You can exclude specific routes from the check by adding them to the `shopsys_administration.access_control.additional_excluded_route_names` configuration parameter in `config/packages/shopsys_administration.yaml`.

## Security Attributes

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
#[CanEdit(methods: [HttpMethod::POST])]   // Process form
public function editAction(Request $request, int $id): Response { }

// Explicit approach
#[Route(path: '/product/edit/{id}')]
#[CanView('ROLE_PRODUCT', [HttpMethod::GET])]    // Show form
#[CanEdit('ROLE_PRODUCT', [HttpMethod::POST])]   // Process form
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

!!! note

    Use `php bin/console shopsys:admin:access-control` to validate your access control coverage. Add `--check` option for CI/CD pipeline validation. Supports filtering with wildcards like `"admin_product_*"` (use quotes for wildcards).

### 1. Class-Level vs Method-Level

- **SuperAdminOnly at class-level** takes precedence over all method-level attributes
- **PublicAccess at class-level** can be overridden by method-level security attributes
- **ForRole** provides default role for CRUD attributes but can be overridden per method
- **On a CRUD controller** `#[ForRole]` sets the role of the whole controller: the built-in CRUD actions and permission attributes on custom routes are all guarded by that role, and the controller does not register its own generated role in the role matrix — pass a role directly in the permission attribute when a single route needs a different one

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
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

#[Route('/product/edit/{id}')]
#[CanView(methods: [HttpMethod::GET])]     // GET requires VIEW
#[CanEdit(methods: [HttpMethod::POST])]    // POST requires EDIT
#[SuperAdminOnly(methods: [HttpMethod::DELETE])] // DELETE requires super admin
public function editAction(Request $request): Response { }
```

## Programmatic Access Control

### AccessChecker Service

The `AccessChecker` service provides the main API for programmatic access control throughout the application:

```php
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessChecker;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

// In controllers, services, etc.
if ($this->accessChecker->canView('ROLE_PRODUCT')) {
    // User can view products
}

// Enforce access (throws exception if denied)
$this->accessChecker->denyUnlessCanEdit('ROLE_ORDER');

// Check route access
if ($this->accessChecker->hasAccessToRoute('admin_product_edit', HttpMethod::POST)) {
    // User can access the route with POST method
}
```

### Twig Template Integration

The `AccessCheckerExtension` provides Twig functions for conditional rendering in templates:

```twig
{# Check permissions #}
{% if can_view('ROLE_PRODUCT') %}
    <a href="{{ path('admin_product_list') }}">Products</a>
{% endif %}

{% if can_edit('ROLE_ORDER') %}
    <button type="submit">Save Order</button>
{% endif %}

{# Check route access with HTTP method #}
{% if has_access_to_route('admin_product_edit', 'POST') %}
    <form method="post">...</form>
{% endif %}
```

**Available Twig Functions:**

- `can_view(roleConstant)` - Check VIEW permission
- `can_edit(roleConstant)` - Check EDIT permission
- `can_create(roleConstant)` - Check CREATE permission
- `can_delete(roleConstant)` - Check DELETE permission
- `has_access_to_route(route, method)` - Check route access with HTTP method

## Menu Integration

The admin menu automatically respects access control rules. Menu items are only shown if the user has access to the linked route. This is handled by `MenuItemsGrantedRolesSubscriber`.

## Simple Permission Mode

For simplified administration, you can enable a streamlined interface that only shows:

- **VIEW** - Read-only access
- **FULL** - All permissions combined

#### Enabling Simple Permission Mode

To enable simple permission mode, add the following to your `config/packages/shopsys_administration.yaml`:

```yaml
shopsys_administration:
    roles:
        # Enable simple permissions (VIEW, FULL) instead of all permissions
        simple_permissions: true
```

!!! info

    In simple permissions mode, only VIEW and FULL columns are displayed in the roles grid. The backend functionality remains unchanged. When FULL is checked: if the role supports FULL permission, it saves FULL; if the role doesn't support FULL, it saves all available permissions for that role instead.

## Best Practices

1. **Use intention-revealing attributes** - `#[CanView('ROLE_PRODUCT')]` is clearer than complex configurations
2. **Use appropriate permissions** - Don't grant EDIT when only VIEW is needed
3. **Test thoroughly** - Validate access control coverage regularly
4. **Document custom roles** - Keep track of any project-specific roles
5. **Follow naming conventions** - Use `ROLE_` prefix for all roles
