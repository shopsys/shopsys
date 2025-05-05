# Admin rights

Administrator rights are implemented using Symfony roles in `config/packages/security.php` (for detailed information, see the [Symfony security documentation](https://symfony.com/doc/current/security.html)).
All the available roles are defined along with their human-readable labels in `Shopsys\FrameworkBundle\Model\Security\Roles` class.
The most important settings in the security config are:

## `role_hierarchy`

- defines the roles' inheritance
- defined by `Shopsys\FrameworkBundle\Model\Security\Roles::getRolesHierarchy()` method
- e.g., entry `ROLE_ORDER_FULL => [ROLE_ORDER_VIEW]` means that by granting the `ROLE_ORDER_FULL` role, the `ROLE_ORDER_VIEW` is granted automatically as well.

## `access_control`

- defines which role has access to which route (or path pattern)
- for the rules defined by the path pattern, the access is evaluated from the top to the bottom, so it is important to define the most nested paths the first
- the rules for the particular routes are defined by `#[AccessControlRule]` attribute above the controller actions, e.g.:

```php
    #[Route(path: '/product/edit/{id}', requirements: ['id' => '\d+'])]
    #[AccessControlRule([Roles::ROLE_PRODUCT_FULL], ['POST'])]
    #[AccessControlRule([Roles::ROLE_PRODUCT_VIEW], ['GET'])]
    public function editAction(Request $request, int $id): Response
```

- the attributes are collected at the container build time and cached in `var/cache/%env%/access_control_rules.json` file, see `Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider` class
- the access control coverage of all the controller actions is checked automatically by the `access-control-rules-check` [phing](../introduction/console-commands-for-application-management-phing-targets.md) target (it is a part of `standards` check)

If a particular page or section is restricted for the given administrator, it is automatically removed from the menu, see `Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemsGrantedRolesSubscriber`.
