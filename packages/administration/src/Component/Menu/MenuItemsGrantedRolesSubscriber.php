<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu;

use Knp\Menu\ItemInterface;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Shopsys\FrameworkBundle\Model\AdminNavigation\RoutingExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MenuItemsGrantedRolesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_ROOT => ['removeNotGrantedItemsFromMenu', -256],
        ];
    }

    public function removeNotGrantedItemsFromMenu(ConfigureMenuEvent $event): void
    {
        $rootMenu = $event->getMenu();
        $this->removeNotGrantedItems($rootMenu);
    }

    private function removeNotGrantedItems(ItemInterface $rootMenu): void
    {
        foreach ($rootMenu as $menuItem) {
            if (!$menuItem->isDisplayed()) {
                continue;
            }

            if (!$this->hasAccessToMenuItemIncludingSubsections($menuItem)) {
                $rootMenu->removeChild($menuItem);

                continue;
            }

            $this->removeNotGrantedItems($menuItem);
        }
    }

    /**
     * Check if user has access to menu item including all its subsections
     * A menu item is accessible if the user has access to it OR any of its children
     */
    private function hasAccessToMenuItemIncludingSubsections(ItemInterface $menuItem): bool
    {
        // Check if user has access to this menu item directly
        if ($this->hasAccessToMenuItem($menuItem)) {
            return true;
        }

        // Check if user has access to any child menu item
        foreach ($menuItem as $childMenuItem) {
            if ($this->hasAccessToMenuItemIncludingSubsections($childMenuItem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has access to a specific menu item
     */
    private function hasAccessToMenuItem(ItemInterface $menuItem): bool
    {
        if (!$menuItem->isDisplayed()) {
            return false;
        }

        $route = $menuItem->getExtra(RoutingExtension::ROUTE_NAME_EXTRA);

        if ($route === null) {
            // Menu items without routes (like category headers) don't have direct access
            // They will only be shown if they have accessible children
            return false;
        }

        return $this->routeAccessChecker->hasAccess($route, HttpMethod::GET);
    }
}
