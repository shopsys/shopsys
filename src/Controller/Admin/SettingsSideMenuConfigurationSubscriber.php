<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SettingsSideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'configureListsSubMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureListsSubMenu(ConfigureMenuEvent $event): void
    {
        $configurationMenu = $event->getMenu();
        $this->hideAvailabilities($configurationMenu);
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function hideAvailabilities(ItemInterface $menu): void
    {
        $listsSubMenu = $menu->getChild('lists');
        $listsSubMenu->removeChild('availabilities');

        $seoMenu = $event->getMenu()->getChild('seo');
        $categorySeoMenu = $seoMenu->addChild('categorySeo', ['route' => 'admin_categoryseo_list', 'label' => t('Rozšířené SEO kategorií')]);
        $categorySeoMenu->addChild('new_category', ['route' => 'admin_categoryseo_newcategory', 'label' => t('Rozšířené SEO kategorií - volba kategorie'), 'display' => false]);
        $categorySeoMenu->addChild('new_filters', ['route' => 'admin_categoryseo_newfilters', 'label' => t('Rozšířené SEO kategorie - filtry'), 'display' => false]);
        $categorySeoMenu->addChild('new_combinations', ['route' => 'admin_categoryseo_newcombinations', 'label' => t('Rozšířené SEO kategorie - kombinace'), 'display' => false]);
    }
}
