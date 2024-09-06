<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Security\MenuItemsGrantedRolesSetting;
use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class SideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_CUSTOMERS => 'configureCustomersMenu',
            ConfigureMenuEvent::SIDE_MENU_MARKETING => 'configureMarketingMenu',
            ConfigureMenuEvent::SIDE_MENU_PRICING => 'configurePricingMenu',
            ConfigureMenuEvent::SIDE_MENU_DASHBOARD => 'configureDashboardMenu',
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'configureSettingsMenu',
            ConfigureMenuEvent::SIDE_MENU_ROOT => 'configureRootMenu',
            ConfigureMenuEvent::SIDE_MENU_ADMINISTRATORS => 'configureAdministratorMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureRootMenu(ConfigureMenuEvent $event)
    {
        $rootMenu = $event->getMenu();
        $rootMenu->addChild($this->createIntegrationsMenu($event));
        $this->removeNotGrantedItemsFromMenu($rootMenu);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureDashboardMenu(ConfigureMenuEvent $event)
    {
        $dashboardMenu = $event->getMenu();
        $dashboardMenu->addChild('transferList', [
            'route' => 'admin_transfer_list',
            'display' => false,
            'label' => t('Transfer issues overview'),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureCustomersMenu(ConfigureMenuEvent $event): void
    {
        $customersMenu = $event->getMenu();
        $customersMenu->setUri(null);
        $customersExtras = $customersMenu->getExtras();
        unset($customersExtras['routes']);
        $customersMenu->setExtras($customersExtras);

        $customersMenu->addChild('customers_overview', [
            'route' => 'admin_customer_list',
            'label' => t('Customers overview'),
        ]);
        $customersMenu->addChild('newsletter', [
            'route' => 'admin_newsletter_list',
            'label' => t('Email newsletter'),
        ]);

        $promoCodeMenu = $customersMenu->addChild('promo_codes', [
            'route' => 'admin_promocode_list',
            'label' => t('Promo codes'),
        ]);
        $promoCodeMenu->addChild('admin_promocode_listmassgeneratebatch', [
            'route' => 'admin_promocode_listmassgeneratebatch',
            'display' => true,
            'label' => t('Generated batches'),
        ]);
        $promoCodeMenu->addChild('promo_codes_new', [
            'route' => 'admin_promocode_new',
            'display' => false,
            'label' => t('New promo code'),
        ]);
        $promoCodeMenu->addChild('promo_codes_edit', [
            'route' => 'admin_promocode_edit',
            'display' => false,
            'label' => t('Editing promo code'),
        ]);
        $promoCodeMenu->addChild('promo_codes_newmassgenerate', [
            'route' => 'admin_promocode_newmassgenerate',
            'label' => t('Bulk creation of promo codes'),
            'display' => false,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configurePricingMenu(ConfigureMenuEvent $event): void
    {
        $pricingMenu = $event->getMenu();
        $pricingMenu->removeChild('promo_codes');
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
        $marketingMenu->removeChild('newsletter');

        $homepageMenu = $marketingMenu->addChild('homepage', ['label' => t('Home page')]);
        $bannersMenu = $homepageMenu->addChild('banners', ['route' => 'admin_slider_list', 'label' => t('Banners')]);
        $bannersMenu->addChild('new_page', ['route' => 'admin_slider_new', 'label' => t('New page'), 'display' => false]);
        $bannersMenu->addChild('edit_page', ['route' => 'admin_slider_edit', 'label' => t('Editing page'), 'display' => false]);

        $homepageMenu->addChild('promoted_products', ['route' => 'admin_topproduct_list', 'label' => t('Promoted products')]);
        $homepageMenu->addChild('promoted_categories', ['route' => 'admin_topcategory_list', 'label' => t('Promoted categories')]);

        $navigationMenu = $marketingMenu->addChild('navigation', ['route' => 'admin_navigation_list', 'label' => t('Navigation')]);
        $navigationMenu->addChild('navigation_edit', ['route' => 'admin_navigation_edit', 'display' => false, 'label' => t('Editing item')]);
        $navigationMenu->addChild('navigation_new', ['route' => 'admin_navigation_new', 'display' => false, 'label' => t('New item')]);

        $notificationBar = $marketingMenu->addChild('notification_bar', ['route' => 'admin_notificationbar_list', 'label' => t('Notification bar')]);
        $notificationBar->addChild('notification_bar_new', ['route' => 'admin_notificationbar_new', 'label' => t('New notification bar'), 'display' => false]);
        $notificationBar->addChild('notification_bar_edit', ['route' => 'admin_notificationbar_edit', 'label' => t('Editing notification bar'), 'display' => false]);

        $marketingMenu->addChild('order_confirmation', ['route' => 'admin_customercommunication_ordersubmitted', 'label' => t('Order confirmation page')]);

        $legalMenu = $marketingMenu->addChild('legal', ['label' => t('Legal conditions')]);
        $legalMenu->addChild('terms_and_conditions', ['route' => 'admin_legalconditions_termsandconditions', 'label' => t('Terms and Conditions')]);
        $legalMenu->addChild('privace_policy', ['route' => 'admin_legalconditions_privacypolicy', 'label' => t('Privacy Policy')]);
        $legalMenu->addChild('personal_data', ['route' => 'admin_personaldata_setting', 'label' => t('Personal data access')]);
        $legalMenu->addChild('cookies', ['route' => 'admin_cookies_setting', 'label' => t('Cookies information')]);
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

        $parameterValueMenu = $listMenu->addChild('parameter_values', ['route' => 'admin_parametervalue_list', 'label' => t('Pararameter value of type color')]);
        $parameterValueMenu->addChild('parameter_values_edit', ['route' => 'admin_parametervalue_edit', 'display' => false, 'label' => t('Editing parameter value of type color')]);

        $transportTypeMenu = $listMenu->addChild('transport_type', ['route' => 'admin_transporttype_list', 'label' => t('Transport types')]);
        $transportTypeMenu->addChild('transport_type_edit', ['route' => 'admin_transporttype_edit', 'display' => false, 'label' => t('Edit transport type')]);

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
            $superadminSettingMenu->addChild(t('Clean Storefront Cache'), ['route' => 'admin_redis_show']);

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
        $integrationsMenu->setExtra('icon', 'gear');

        $integrationsMenu->addChild('feeds', ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);

        $heurekaMenu = $integrationsMenu->addChild('heureka', ['label' => t('Heureka')]);
        $heurekaMenu->addChild('settings', ['route' => 'admin_heureka_setting', 'label' => t('Heureka')]);

        return $integrationsMenu;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureAdministratorMenu(ConfigureMenuEvent $event): void
    {
        $administratorMenu = $event->getMenu();

        $administratorMenu->setUri(null);
        $administratorExtras = $administratorMenu->getExtras();
        unset($administratorExtras['routes']);
        $administratorMenu->setExtras($administratorExtras);

        $administratorViewMenu = $administratorMenu->addChild('administrator_view', ['route' => 'admin_administrator_list', 'label' => t('Administrators overview')]);
        $administratorViewMenu->addChild(
            'new',
            ['route' => 'admin_administrator_new', 'label' => t('New administrator'), 'display' => false],
        );
        $administratorViewMenu->addChild(
            'edit',
            ['route' => 'admin_administrator_edit', 'label' => t('Editing administrator'), 'display' => false],
        );

        $administratorRoleGroupMenu = $administratorMenu->addChild('role_groups', ['route' => 'admin_administratorrolegroup_list', 'label' => t('Role Groups')]);

        $administratorRoleGroupMenu->addChild(
            'new',
            ['route' => 'admin_administratorrolegroup_new', 'label' => t('New administrator role group'), 'display' => false],
        );
        $administratorRoleGroupMenu->addChild(
            'edit',
            ['route' => 'admin_administratorrolegroup_edit', 'label' => t('Editing administrator role group'), 'display' => false],
        );
        $administratorRoleGroupMenu->addChild(
            'copy',
            ['route' => 'admin_administratorrolegroup_copy', 'label' => t('Copy administrator role group'), 'display' => false],
        );
    }

    /**
     * @param \Knp\Menu\ItemInterface $rootMenu
     */
    public function removeNotGrantedItemsFromMenu(ItemInterface $rootMenu): void
    {
        foreach (MenuItemsGrantedRolesSetting::getGrantedRolesByMenuItems() as $menuItemPath => $grantedRoles) {
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
    private function removeItemFromMenu(string $itemToRemovePath, ItemInterface $rootMenu): void
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
