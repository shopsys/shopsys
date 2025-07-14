<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\ItemInterface;
use Override;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuItemsGrantedRolesSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<string, string[]>|null
     */
    protected ?array $rolesIndexedByRoute = null;

    /**
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider $routeAccessControlDataProvider
     */
    public function __construct(
        protected readonly Security $security,
        protected readonly RouteAccessControlDataProvider $routeAccessControlDataProvider,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_ROOT => ['removeNotGrantedItemsFromMenu', -256],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function removeNotGrantedItemsFromMenu(ConfigureMenuEvent $event): void
    {
        $rootMenu = $event->getMenu();
        $this->removeNotGrantedItems($rootMenu);
    }

    /**
     * @param \Knp\Menu\ItemInterface $rootMenu
     */
    protected function removeNotGrantedItems(ItemInterface $rootMenu): void
    {
        foreach ($rootMenu as $menuItem) {
            if (!$menuItem->isDisplayed()) {
                continue;
            }

            $requiredRoles = $this->getRequiredRolesForMenuItemIncludingSubsections($menuItem);
            $isGranted = $this->isUserGrantedAnyOfRoles($requiredRoles);

            if (!$isGranted) {
                $rootMenu->removeChild($menuItem);
            }

            $this->removeNotGrantedItems($menuItem);
        }
    }

    /**
     * @param string[] $requiredRoles
     * @return bool
     */
    protected function isUserGrantedAnyOfRoles(array $requiredRoles): bool
    {
        if ($requiredRoles === []) {
            return true;
        }

        foreach ($requiredRoles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Knp\Menu\ItemInterface $menuItem
     * @return string[]
     */
    protected function getRequiredRolesForMenuItemIncludingSubsections(ItemInterface $menuItem): array
    {
        $roles = $this->getRequiredRolesForMenuItem($menuItem);

        foreach ($menuItem->getChildren() as $childMenuItem) {
            $roles = [...$roles, ...$this->getRequiredRolesForMenuItemIncludingSubsections($childMenuItem)];
        }

        return array_unique($roles);
    }

    /**
     * @param \Knp\Menu\ItemInterface $menuItem
     * @return string[]
     */
    protected function getRequiredRolesForMenuItem(ItemInterface $menuItem): array
    {
        if ($menuItem->isDisplayed() === false) {
            return [];
        }

        $route = $menuItem->getExtra(RoutingExtension::ROUTE_NAME_EXTRA);

        if ($route === null) {
            return [];
        }

        $rolesIndexedByRoute = $this->getRolesIndexedByRoute();

        return $rolesIndexedByRoute[$route] ?? [];
    }

    /**
     * @return array<string, string[]>
     */
    protected function getRolesIndexedByRoute(): array
    {
        if ($this->rolesIndexedByRoute !== null) {
            return $this->rolesIndexedByRoute;
        }

        $this->rolesIndexedByRoute = [];

        foreach ($this->routeAccessControlDataProvider->findAll() as $routeAccessControlData) {
            $routeName = $routeAccessControlData->routeName;

            if (array_key_exists($routeName, $this->rolesIndexedByRoute)) {
                $this->rolesIndexedByRoute[$routeName] = [...$this->rolesIndexedByRoute[$routeName], ...$routeAccessControlData->accessControlRule->roles];
            } else {
                $this->rolesIndexedByRoute[$routeName] = $routeAccessControlData->accessControlRule->roles;
            }
        }

        return $this->rolesIndexedByRoute;
    }
}
