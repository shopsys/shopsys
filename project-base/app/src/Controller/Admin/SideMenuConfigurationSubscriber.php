<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_MARKETING => 'configureMarketingMenu',
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'configureSettingsMenu',
            ConfigureMenuEvent::SIDE_MENU_ROOT => 'configureRootMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureRootMenu(ConfigureMenuEvent $event)
    {
        $rootMenu = $event->getMenu();
        $rootMenu->addChild($this->createIntegrationsMenu($event));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureMarketingMenu(ConfigureMenuEvent $event): void
    {
        $marketingMenu = $event->getMenu();
        $marketingMenu->setLabel(t('CMS'));

        $marketingMenu->removeChild('slider');
        $marketingMenu->removeChild('top_products');
        $marketingMenu->removeChild('top_categories');
        $marketingMenu->removeChild('feeds');

        $homepageMenu = $marketingMenu->addChild('homepage', ['label' => t('Home page')]);
        $bannersMenu = $homepageMenu->addChild('banners', ['route' => 'admin_slider_list', 'label' => t('Banners')]);
        $bannersMenu->addChild('new_page', ['route' => 'admin_slider_new', 'label' => t('New page'), 'display' => false]);
        $bannersMenu->addChild('edit_page', ['route' => 'admin_slider_edit', 'label' => t('Editing page'), 'display' => false]);

        $homepageMenu->addChild('promoted_products', ['route' => 'admin_topproduct_list', 'label' => t('Promoted products')]);
        $homepageMenu->addChild('promoted_categories', ['route' => 'admin_topcategory_list', 'label' => t('Promoted categories')]);

        $notificationBar = $marketingMenu->addChild('notification_bar', ['route' => 'admin_notificationbar_list', 'label' => t('Notification bar')]);
        $notificationBar->addChild('notification_bar_new', ['route' => 'admin_notificationbar_new', 'label' => t('New notification bar'), 'display' => false]);
        $notificationBar->addChild('notification_bar_edit', ['route' => 'admin_notificationbar_edit', 'label' => t('Editing notification bar'), 'display' => false]);

        $marketingMenu->addChild('order_confirmation', ['route' => 'admin_customercommunication_ordersubmitted', 'label' => t('Order confirmation page')]);

        $legalMenu = $marketingMenu->addChild('legal', ['label' => t('Legal conditions')]);
        $legalMenu->addChild('terms_and_conditions', ['route' => 'admin_legalconditions_termsandconditions', 'label' => t('Terms and Conditions')]);
        $legalMenu->addChild('privace_policy', ['route' => 'admin_legalconditions_privacypolicy', 'label' => t('Privacy Policy')]);
        $legalMenu->addChild('personal_data', ['route' => 'admin_personaldata_setting', 'label' => t('Personal data access')]);
        $legalMenu->addChild('user_consent_policy', ['route' => 'admin_userconsentpolicy_setting', 'label' => t('User consent policy')]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureSettingsMenu(ConfigureMenuEvent $event): void
    {
        $settingsMenu = $event->getMenu();
        $settingsMenu->removeChild('heureka');
        $settingsMenu->removeChild('legal');
        $settingsMenu->getChild('communication')->removeChild('order_confirmation');

        $seoMenu = $settingsMenu->getChild('seo');
        $categorySeoMenu = $seoMenu->addChild('categorySeo', ['route' => 'admin_categoryseo_list', 'label' => t('Extended SEO categories')]);
        $categorySeoMenu->addChild('new_category', ['route' => 'admin_categoryseo_newcategory', 'label' => t('Extended SEO category - category selection'), 'display' => false]);
        $categorySeoMenu->addChild('new_filters', ['route' => 'admin_categoryseo_newfilters', 'label' => t('Extended SEO category - filters'), 'display' => false]);
        $categorySeoMenu->addChild('new_combinations', ['route' => 'admin_categoryseo_newcombinations', 'label' => t('Extended SEO category - combinations'), 'display' => false]);
        $categorySeoMenu->addChild('new_combination', ['route' => 'admin_categoryseo_readycombination', 'label' => t('Extended SEO category - set combinations with SEO values'), 'display' => false]);

        $listMenu = $settingsMenu->getChild('lists');
        $listMenu->removeChild('availabilities');
        $listMenu->getChild('units')->setLabel(t('Measurement units'));

        $flagsMenu = $listMenu->getChild('flags');
        $flagsMenu->addChild('flagNew', ['route' => 'admin_flag_new', 'label' => t('New flag'), 'display' => false]);
        $flagsMenu->addChild('flagEdit', ['route' => 'admin_flag_edit', 'label' => t('Editing flag'), 'display' => false]);

        $storeMenu = $listMenu->addChild('stores', ['route' => 'admin_store_list', 'label' => t('Stores')]);
        $storeMenu->addChild('new_store', ['route' => 'admin_store_new', 'display' => false, 'label' => t('New store')]);
        $storeMenu->addChild('edit_store', ['route' => 'admin_store_edit', 'display' => false, 'label' => t('Edit store')]);

        $stockMenu = $settingsMenu->addChild('stocks', ['label' => t('Stocking')]);
        $stockMenu->addChild('stock', ['route' => 'admin_stock_list', 'label' => t('Warehouses')]);
        $stockMenu->addChild('new_stock', ['route' => 'admin_stock_new', 'display' => false, 'label' => t('New warehouse')]);
        $stockMenu->addChild('edit_stock', ['route' => 'admin_stock_edit', 'display' => false, 'label' => t('Warehouse detail')]);
        $stockMenu->addChild('stock_settings', ['route' => 'admin_stock_settings', 'label' => t('Warehouse settings')]);

        $closedDayMenu = $listMenu->addChild('closed_day', ['route' => 'admin_closedday_list', 'label' => t('Holidays and internal days')]);
        $closedDayMenu->addChild('closed_day_new', ['route' => 'admin_closedday_new', 'label' => t('New closed day'), 'display' => false]);
        $closedDayMenu->addChild('closed_day_edit', ['route' => 'admin_closedday_edit', 'label' => t('Holiday / internal day detail'), 'display' => false]);

        $superadminSettingMenu = $settingsMenu->getChild('superadmin');

        if ($superadminSettingMenu !== null) {
            $superadminSettingMenu->addChild('cspHeader', ['route' => 'admin_cspheader_setting', 'label' => t('Content-Security-Policy header')]);

            $settingsMenu->removeChild('superadmin');
            $settingsMenu->addChild($superadminSettingMenu);
        }

        $constantsMenu = $settingsMenu->addChild('constants', ['label' => t('Language constants')]);
        $constantsListMenu = $constantsMenu->addChild('constants_list', ['route' => 'admin_languageconstant_list', 'label' => t('List of language constants')]);
        $constantsListMenu->addChild('constants_edit', ['route' => 'admin_languageconstant_edit', 'label' => t('Language constant translation'), 'display' => false]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     * @return \Knp\Menu\ItemInterface
     */
    protected function createIntegrationsMenu(ConfigureMenuEvent $event): ItemInterface
    {
        $integrationsMenu = $event->getMenuFactory()->createItem('integrations', ['label' => t('Integrations')]);
        $integrationsMenu->setExtra('icon', 'plugin');

        $integrationsMenu->addChild('feeds', ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);

        $heurekaMenu = $integrationsMenu->addChild('heureka', ['label' => t('Heureka')]);
        $heurekaMenu->addChild('settings', ['route' => 'admin_heureka_setting', 'label' => t('Heureka')]);

        return $integrationsMenu;
    }
}
