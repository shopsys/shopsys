<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu;

use Knp\Menu\ItemInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CrudMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly CrudControllerRegistry $crudControllerRegistry,
        public readonly CrudRouteProvider $crudRouteProvider,
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

        foreach ($this->crudControllerRegistry->getItems() as $item) {
            $config = $item->getConfig();

            if ($config->isFullDisabled()) {
                continue;
            }

            $sectionMenu = $config->getMenuSection();

            $menu = $this->findMenuItem($rootMenu, $sectionMenu);

            if ($menu === null) {
                return;
            }

            $submenuSection = $config->getSubmenuSection();

            if ($submenuSection !== null) {
                $menu = $menu->getChild($submenuSection);
            }

            $route = $this->crudRouteProvider->generate($item, ActionType::LIST);
            $parent = $menu->addChild($route->getRouteName(), [
                'route' => $route->getRouteName(),
                'display' => $config->isVisibleInMenu(),
                'label' => $config->getMenuTitle(),
            ]);

            foreach ($config->getActions() as $action) {
                if ($action === ActionType::DELETE) {
                    continue;
                }

                $route = $this->crudRouteProvider->generate($item, $action);

                $parent->addChild($route->getRouteName(), [
                    'route' => $route->getRouteName(),
                    'display' => false,
                    'label' => $config->getTitle($action),
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
