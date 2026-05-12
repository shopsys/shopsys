# Administration Menu

The admin side menu is implemented by [KnpMenuBundle](https://symfony.com/doc/master/bundles/KnpMenuBundle/index.html), and to extend it, you can use [events](https://symfony.com/doc/master/bundles/KnpMenuBundle/events.html).
An example of such an extension is described in the cookbook [Adding a New Administration Page](../cookbook/adding-a-new-administration-page.md).

To see how the side menu works programmatically, you can see the [`SideMenuBuilder`]({{github.link}}/packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php) class where it is created.
The side menu builder is tagged with `knp_menu.menu_builder` and is accessible under the alias `admin_side_menu`.

There are a few customizations on top of the standard [KnpMenu](https://symfony.com/doc/master/bundles/KnpMenuBundle/index.html):

## Template

The template is not configured globally (via `config/packages/twig.yaml`) but instead, the template is provided during the rendering in a Twig template:

```twig
{{ knp_menu_render('admin_side_menu', {template: '@ShopsysAdministration/partial/side_menu.html.twig'}) }}`.
```

The menu template works similarly to the default `knp_menu.html.twig`, but it uses BEM classes and supports a few custom features.

## Javascript

The behavior of the side menu is controlled via the JS component [`Shopsys.sideMenu`]({{github.link}}/packages/framework/assets/js/admin/components/SideMenu.js).

## Icons

There is an extra attribute `icon` supported to allow icons in the menu.
Currently, it's used only for the first level of menu items, but it will also work when set to nested items.

It can be assigned to a menu item in an event subscriber by calling, e.g.

```php
$menuItem->setExtra('icon', 'cart');
```

## Superadmin access

There is an extra boolean attribute `superadmin` supported to allow highlighting of restricted access of the menu.

It's only used for the visual effect. The restriction itself has to be done manually using security attributes like `#[SuperAdminOnly]` on the controller action, see the [separate article](./admin-rights.md) for more information.

## Events

After building the whole menu a `ConfigureMenuEvent::SIDE_MENU_ROOT` event is dispatched.
After adding each of the submenus, a different event is dispatched.
You can take a look at the class [`ConfigureMenuEvent`]({{github.link}}/packages/framework/src/Model/AdminNavigation/ConfigureMenuEvent.php) to see all the events and their internals.

These can be used to [extend the menu](https://symfony.com/doc/master/bundles/KnpMenuBundle/events.html), either from the project repository or modules.

## Routing extension

To render the admin breadcrumb navigation, the menu items are used as well.
For this reason, even pages that are not displayed in the menu are added to it (with a `display` attribute set to `false`).

These items can have a configured route without some mandatory parameters filled in (e.g., a product edit page without a product ID parameter).
To avoid throwing the `MissingMandatoryParametersException`, we have replaced the original `RoutingExtension` with [our own implementation]({{github.link}}/packages/framework/src/Model/AdminNavigation/RoutingExtension.php).
It simply doesn't generate a URI when the exception is thrown.
The route is still used to resolve the current menu item.

## Autocomplete search

The admin autocomplete search also uses menu items as its source.
An item is included in search results when:

- it has a label
- it has a generated URI

This means hidden menu items (`display => false`) are searchable by default.
That is useful for pages like "new" actions, which are intentionally hidden from the side menu but are still valid standalone admin pages.

Routes with mandatory route parameters but without concrete values usually have no generated URI because of the custom routing extension above, so they do not appear in the autocomplete.

## Breadcrumb overriding

A `BreadcrumbOverrider::overrideLastItem(string $label)` call can be used in a controller to override the last breadcrumb item.
This can be used, for example, to specify which product you're editing in the breadcrumb navigation.
