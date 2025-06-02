# Adding a New Administration Page

In this cookbook, we will add a new page to the administration, namely a new dashboard page with X.com updates.
We will see how to create a new admin controller, what template to extend, and how to extend the menu along with the breadcrumb navigation.
We will also cover how to set up access control for the new page.

## New admin controller

The first step to creating a new page is creating a controller (for details, see [Symfony Controller documentation](https://symfony.com/doc/3.4/controller.html)).
Create a class extending `AdminBaseController` in `src/Controller/Admin` directory with a single method (action):

```php
namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AdminBaseController
{
    #[Route('/dashboard/x/')]
    public function xAction()
    {
        return $this->render('Admin/Content/Dashboard/x.html.twig');
    }
}
```

The `Admin` directory is already configured to use [routing by attributes](https://symfony.com/doc/current/routing.html#creating-routes-as-attributes), as it is the easiest to use.
By adding the `#[Route('/dashboard/x/')]` attribute, you are creating a route named `admin_dashboard_x` (`admin_` + lowercase controller name + `_` + lowercase action name).

This newly added route should be available under the URL [http://127.0.0.1:8000/admin/**dashboard/x/**](http://127.0.0.1:8000/admin/dashboard/x/) by default.
If you try to access the page, it will fail on loading a non-existing template, which we will fix in the next step.

If you'd like to create something more complicated, you can require other services in the controller's constructor, which will be autowired.

## Twig template

Create a new Twig template named `x.html.twig` in `templates/Admin/Content` (you'll have to create the directory).

The template should extend `'@ShopsysAdministration/layout/layout_with_panel.html.twig` and extend its blocks `title`, `h1` and `block main_content`:

```twig
{% extends '@ShopsysAdministration/layout/layout_with_panel.html.twig' %}

{% block title %}- {{ 'Tweets by @shopsyscz'|trans }}{% endblock %}
{% block h1 %}{{ 'Updates from Shopsys Platform'|trans }}{% endblock %}

{% block main_content %}
    <a class="x-timeline" data-lang="{{ app.request.locale }}" data-theme="light" href="https://x.com/shopsyscz"></a>
    <script async src="https://platform.x.com/widgets.js" charset="utf-8"></script>
{% endblock %}
```

The page's content is just a simple [X.com widget](https://publish.x.com/), but you can put any content on your page.
You can use the controller to pass some parameters to your template.
Feel free to examine other controllers for inspiration.

If you're new to Twig, you can take a look at [Symfony Templating documentation](http://symfony.com/doc/current/templating.html).

Now, the page should load correctly and display the newest tweets of [@shopsyscz](https://x.com/shopsyscz).
But to access it, you still need to open a specific URL...

## Side menu and breadcrumbs

The admin side menu is implemented by [KnpMenuBundle](https://symfony.com/doc/master/bundles/KnpMenuBundle/index.html) and to extend it, you can use [events](https://symfony.com/doc/master/bundles/KnpMenuBundle/events.html).
For details about the menu customization, read the [Administration Menu](../administration/administration-menu.md) article.

Right now, the Dashboard menu looks like this:

![Dashboard admin menu before the modification](img/dashboard-menu-before.png)

Create a new [event subscriber](https://symfony.com/doc/current/event_dispatcher.html) and subscribe to the `ConfigureMenuEvent::SIDE_MENU_DASHBOARD` event.
This is an event that will allow you to reconfigure the Dashboard menu and add a new item there.
You can take a look at the class [`ConfigureMenuEvent`]({{github.link}}/packages/framework/src/Model/AdminNavigation/ConfigureMenuEvent.php) to see other events you can subscribe to.

You can put the subscriber right beside your new controller.

In the subscriber, you should add a new child to the menu with the route of your new page.
As the Dashboard menu currently has no children, you can remove the link and add a new child with the original dashboard, so it's still accessible:

```php
namespace App\Controller\Admin;

use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ConfigureMenuEvent::SIDE_MENU_DASHBOARD => 'configureDashboardMenu'];
    }

    public function configureDashboardMenu(ConfigureMenuEvent $event): void
    {
        $dashboardMenu = $event->getMenu();

        $dashboardMenu->addChild('default', ['route' => 'admin_default_dashboard', 'label' => t('Default dashboard')]);
        $dashboardMenu->addChild('twitter', ['route' => 'admin_dashboard_x', 'label' => t('Tweets by @shopsyscz')]);

        $this->removeLink($dashboardMenu);
    }

    private function removeLink(ItemInterface $item): void
    {
        $item->setExtra('routes', []);
        $item->setUri(null);
    }
}
```

The event subscriber should be auto-discovered by Symfony, reconfiguring the menu and resulting in this structure:

![Dashboard admin menu after the modification](img/dashboard-menu-after.png)

## Access control

When adding a new administrator page, you need to decide which [administrator roles](../administration/admin-rights.md) should have access to it.
For the new agenda, you will probably need to create a new role. Usually, a `VIEW`/`FULL` pair of roles is used, so let's follow the convention here as well. In `Roles.php`, add the new roles, define theirs translations, and put them into the hierarchy:

```php

<?php

declare(strict_types=1);

namespace App\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const string ROLE_X_VIEW = 'ROLE_X_VIEW';
    public const string ROLE_X_FULL = 'ROLE_X_FULL';

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected static function addRolesToHierarchy(array $rolesHierarchy): array
    {
         return static::addRolePairsToHierarchy($rolesHierarchy, [static::ROLE_X_FULL => static::ROLE_X_VIEW]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function addRolesToGrid(array $rolesGrid): array
    {
        $rolePair = [
            static::ROLE_X_FULL => t('X.com feed - full'),
            static::ROLE_X_VIEW => t('X.com feed - view'),
        ];

        return $this->addRolePairsToGrid($rolesGrid, [$rolePair]);
    }
}

```

After that, you need to cover your new route with the access control rules. Let's say that the administrator needs to have full rights to perform POST requests and view rights for GET requests:

```diff
// src/Controller/Admin/DashboardController.php

+ use App\Model\Security\Roles;
+ use Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRule;

    #[Route('/dashboard/x/')]
+   #[AccessControlRule([Roles::ROLE_X_FULL], ['POST'])]
+   #[AccessControlRule([Roles::ROLE_X_VIEW], ['GET'])]
    public function xAction()
    {
        return $this->render('Admin/Content/Dashboard/x.html.twig');
    }
```

## Conclusion

We've seen how to add a new simple page into the administration with a route and its own place in the side menu.
A similar approach could be used to add more complicated parametrized pages using other services or forms.

Also, we've not only added a new item to the menu, but we've modified some parameters of an already existing menu item, removing the link from it.
This can be used to alter the menu in a more significant way.

Finally, we have added access control rules to the new page, so it is only accessible to users with the appropriate roles.

To see how the side menu works, you can see the [`SideMenuBuilder`]({{github.link}}/packages/framework/src/Model/AdminNavigation/SideMenuBuilder.php) class where the menu is created.
