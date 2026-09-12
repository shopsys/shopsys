<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu;

use Knp\Menu\ItemInterface;
use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemPositioner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CrudMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly CrudControllerRegistry $crudControllerRegistry,
        public readonly CrudRouteProvider $crudRouteProvider,
        private readonly MenuItemPositioner $menuItemPositioner,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_ROOT => ['onConfigureMenu', -200],
        ];
    }

    public function onConfigureMenu(ConfigureMenuEvent $event): void
    {
        $rootMenu = $event->getMenu();

        foreach ($this->crudControllerRegistry->getAll() as $item) {
            $config = $item->config;

            if ($config->isFullDisabled()) {
                continue;
            }

            $sectionMenu = $config->getMenuSection();

            $menu = $this->findMenuItem($rootMenu, $sectionMenu);

            if ($menu === null) {
                // a missing section hides only the item of this controller, the remaining CRUD controllers are still added
                continue;
            }

            $submenuSection = $config->getSubmenuSection();

            if ($submenuSection !== null) {
                $menu = $menu->getChild($submenuSection) ?? throw new LogicException(sprintf(
                    'CRUD controller "%s" is configured to be displayed in submenu section "%s" of menu section "%s", but the section has no such child. Check the setMenuSection() call in its configure() method.',
                    $item->controllerClass,
                    $submenuSection,
                    $sectionMenu,
                ));
            }

            $route = $this->crudRouteProvider->getRouteItem($item->controllerClass, ActionType::LIST);
            $parent = $this->menuItemPositioner->addChild(
                $menu,
                $route->getRouteName(),
                [
                    'route' => $route->getRouteName(),
                    'display' => $config->isVisibleInMenu(),
                    'label' => $config->getMenuTitle(),
                ],
                $config->getMenuSectionPosition(),
            );

            if ($config->getMenuIcon() !== null && $menu === $rootMenu) {
                $parent->setExtra('icon', $config->getMenuIcon());
            }

            foreach ($config->getActions() as $action) {
                if ($action->isSubMenuRouteItem() === false) {
                    continue;
                }

                $route = $this->crudRouteProvider->getRouteItem($item->controllerClass, $action);

                $parent->addChild($route->getRouteName(), [
                    'route' => $route->getRouteName(),
                    'display' => false,
                    'label' => $config->getBreadcrumbTitle($action),
                ]);
            }
        }
    }

    private function findMenuItem(ItemInterface $rootMenu, string $menuSectionName): ?ItemInterface
    {
        if ($rootMenu->getName() === $menuSectionName) {
            return $rootMenu;
        }

        foreach ($rootMenu->getChildren() as $child) {
            $menuItem = $this->findMenuItem($child, $menuSectionName);

            if ($menuItem !== null) {
                return $menuItem;
            }
        }

        return null;
    }
}
