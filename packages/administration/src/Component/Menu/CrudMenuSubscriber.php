<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu;

use Knp\Menu\ItemInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CrudMenuSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider $crudConfigProvider
     */
    public function __construct(
        public readonly CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry,
        public readonly CrudRouteProvider $crudRouteProvider,
        public readonly CrudConfigProvider $crudConfigProvider,
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function onConfigureMenu(ConfigureMenuEvent $event): void
    {
        $rootMenu = $event->getMenu();

        foreach ($this->crudControllerDefinitionRegistry->getItems() as $item) {
            $config = $this->crudConfigProvider->getConfig($item);

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

    /**
     * @param \Knp\Menu\ItemInterface $rootMenu
     * @param string $menuSectionName
     * @return \Knp\Menu\ItemInterface|null
     */
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
