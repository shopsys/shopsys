<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Model\Security\MenuItemsGrantedRolesSetting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class MenuItemsGrantedRolesSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Security\MenuItemsGrantedRolesSetting $menuItemsGrantedRolesSetting
     */
    public function __construct(
        protected readonly Security $security,
        protected readonly MenuItemsGrantedRolesSetting $menuItemsGrantedRolesSetting,
    ) {
    }

    /**
     * @return array
     */
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

        foreach ($this->menuItemsGrantedRolesSetting->getGrantedRolesByMenuItems() as $menuItemPath => $grantedRoles) {
            $isGranted = array_reduce(
                $grantedRoles,
                fn ($isGranted, $role) => $isGranted || $this->security->isGranted($role),
                false,
            );

            if (!$isGranted) {
                $this->removeItemFromMenu($menuItemPath, $rootMenu);
            }
        }
    }

    /**
     * @param string $itemToRemovePath
     * @param \Knp\Menu\ItemInterface $rootMenu
     */
    protected function removeItemFromMenu(string $itemToRemovePath, ItemInterface $rootMenu): void
    {
        $itemToRemovePathExploded = explode(MenuItemsGrantedRolesSetting::MENU_ITEM_PATH_SEPARATOR, $itemToRemovePath);
        $itemToRemoveName = end($itemToRemovePathExploded);

        foreach ($itemToRemovePathExploded as $itemName) {
            if ($rootMenu === null) {
                break;
            }

            if ($itemName === $itemToRemoveName) {
                $rootMenu->removeChild($itemName);

                break;
            }
            $rootMenu = $rootMenu->getChild($itemName);
        }
    }
}
