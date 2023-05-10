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
     * @var \Symfony\Component\Security\Core\Security
     */
    private Security $security;

    /**
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
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
            'label' => t('Přehled problémů v přenosech'),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureCustomersMenu(ConfigureMenuEvent $event): void
    {
        $customersMenu = $event->getMenu();
        $customersMenu->setUri('');
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
            'label' => t('Slevové kupóny'),
        ]);
        $promoCodeMenu->addChild('admin_promocode_listmassgeneratebatch', [
            'route' => 'admin_promocode_listmassgeneratebatch',
            'display' => true,
            'label' => t('Vygenerované dávky'),
        ]);
        $promoCodeMenu->addChild('promo_codes_new', [
            'route' => 'admin_promocode_new',
            'display' => false,
            'label' => t('Nový slevový kupóny'),
        ]);
        $promoCodeMenu->addChild('promo_codes_edit', [
            'route' => 'admin_promocode_edit',
            'display' => false,
            'label' => t('Editace slevového kupónu'),
        ]);
        $promoCodeMenu->addChild('promo_codes_newmassgenerate', [
            'route' => 'admin_promocode_newmassgenerate',
            'label' => t('Hromadné vytvoření slevových kupónů'),
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
        $navigationMenu->addChild('navigation_edit', ['route' => 'admin_navigation_edit', 'display' => false, 'label' => t('Editace položky')]);
        $navigationMenu->addChild('navigation_new', ['route' => 'admin_navigation_new', 'display' => false, 'label' => t('Nová položka')]);

        $blogMenu = $marketingMenu->addChild('blog', ['label' => t('Blog')]);

        $blogCategories = $blogMenu->addChild('blogCategories', ['route' => 'admin_blogcategory_list', 'label' => t('Rubriky blogu')]);
        $blogCategories->addChild('newBlogCategories', ['route' => 'admin_blogcategory_new', 'display' => false, 'label' => t('Nová rubrika blogu')]);
        $blogCategories->addChild('editBlogCategories', ['route' => 'admin_blogcategory_edit', 'display' => false]);

        $blogArticles = $blogMenu->addChild('blogArticles', ['route' => 'admin_blogarticle_list', 'label' => t('Články blogu')]);
        $blogArticles->addChild('newBlogArticles', ['route' => 'admin_blogarticle_new', 'display' => false, 'label' => t('Nový článek blogu')]);
        $blogArticles->addChild('editBlogArticles', ['route' => 'admin_blogarticle_edit', 'display' => false]);

        $notificationBar = $marketingMenu->addChild('notification_bar', ['route' => 'admin_notificationbar_list', 'label' => t('Notifikační lišta')]);
        $notificationBar->addChild('notification_bar_new', ['route' => 'admin_notificationbar_new', 'label' => t('Nová notifikační lišta'), 'display' => false]);
        $notificationBar->addChild('notification_bar_edit', ['route' => 'admin_notificationbar_edit', 'label' => t('Editace notifikační lišty'), 'display' => false]);

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
        $settingsMenu->removeChild('external_scripts');
        $settingsMenu->removeChild('legal');
        $settingsMenu->getChild('communication')->removeChild('order_confirmation');

        $seoMenu = $settingsMenu->getChild('seo');
        $categorySeoMenu = $seoMenu->addChild('categorySeo', ['route' => 'admin_categoryseo_list', 'label' => t('Rozšířené SEO kategorií')]);
        $categorySeoMenu->addChild('new_category', ['route' => 'admin_categoryseo_newcategory', 'label' => t('Rozšířené SEO kategorií - volba kategorie'), 'display' => false]);
        $categorySeoMenu->addChild('new_filters', ['route' => 'admin_categoryseo_newfilters', 'label' => t('Rozšířené SEO kategorie - filtry'), 'display' => false]);
        $categorySeoMenu->addChild('new_combinations', ['route' => 'admin_categoryseo_newcombinations', 'label' => t('Rozšířené SEO kategorie - kombinace'), 'display' => false]);
        $categorySeoMenu->addChild('new_combination', ['route' => 'admin_categoryseo_readycombination', 'label' => t('Rozšířené SEO kategorie - nastavení kombinace se SEO hodnotami'), 'display' => false]);
        $seoMenu->addChild('unusedFriendlyUrlList', ['route' => 'admin_unused_friendly_url_list', 'label' => t('Unused friendly URL list')]);

        $seoPageMenu = $seoMenu->addChild('seoPageList', ['route' => 'admin_seopage_list', 'label' => t('SEO pages')]);
        $seoPageMenu->addChild('seoPageNew', ['route' => 'admin_seopage_new', 'label' => t('New SEO page'), 'display' => false]);
        $seoPageMenu->addChild('seoPageEdit', ['route' => 'admin_seopage_edit', 'label' => t('Editing SEO page'), 'display' => false]);

        $listMenu = $settingsMenu->getChild('lists');
        $listMenu->removeChild('availabilities');
        $listMenu->getChild('units')->setLabel(t('Measurement units'));

        $flagsMenu = $listMenu->getChild('flags');
        $flagsMenu->addChild('flagNew', ['route' => 'admin_flag_new', 'label' => t('New flag'), 'display' => false]);
        $flagsMenu->addChild('flagEdit', ['route' => 'admin_flag_edit', 'label' => t('Editing flag'), 'display' => false]);

        $storeMenu = $listMenu->addChild('stores', ['route' => 'admin_store_list', 'label' => t('Stores')]);
        $storeMenu->addChild('new_store', ['route' => 'admin_store_new', 'display' => false, 'label' => t('New store')]);
        $storeMenu->addChild('edit_store', ['route' => 'admin_store_edit', 'display' => false, 'label' => t('Edit store')]);

        $parameterValueMenu = $listMenu->addChild('parameter_values', ['route' => 'admin_parametervalue_list', 'label' => t('Hodnota parametru typu barva')]);
        $parameterValueMenu->addChild('parameter_values_edit', ['route' => 'admin_parametervalue_edit', 'display' => false, 'label' => t('Editace hodnoty parametru typu barva')]);

        $transportTypeMenu = $listMenu->addChild('transport_type', ['route' => 'admin_transporttype_list', 'label' => t('Transport types')]);
        $transportTypeMenu->addChild('transport_type_edit', ['route' => 'admin_transporttype_edit', 'display' => false, 'label' => t('Edit transport type')]);

        $stockMenu = $settingsMenu->addChild('stocks', ['label' => t('Skladovost')]);
        $stockMenu->addChild('stock', ['route' => 'admin_stock_list', 'label' => t('Sklady')]);
        $stockMenu->addChild('new_stock', ['route' => 'admin_stock_new', 'display' => false, 'label' => t('Nový sklad')]);
        $stockMenu->addChild('edit_stock', ['route' => 'admin_stock_edit', 'display' => false, 'label' => t('Detail skladu')]);
        $stockMenu->addChild('stock_settings', ['route' => 'admin_stock_settings', 'label' => t('Nastavení skladů')]);

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

        $externalScriptsMenu = $integrationsMenu->addChild('external_scripts', ['label' => t('External scripts')]);
        $scriptsMenu = $externalScriptsMenu->addChild('scripts', ['route' => 'admin_script_list', 'label' => t('Scripts overview')]);
        $scriptsMenu->addChild('new', ['route' => 'admin_script_new', 'label' => t('New script'), 'display' => false]);
        $scriptsMenu->addChild('edit', ['route' => 'admin_script_edit', 'label' => t('Editing script'), 'display' => false]);
        $externalScriptsMenu->addChild('google_analytics', ['route' => 'admin_script_googleanalytics', 'label' => t('Google analytics')]);

        return $integrationsMenu;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureAdministratorMenu(ConfigureMenuEvent $event): void
    {
        $administratorMenu = $event->getMenu();

        $administratorMenu->setUri('');
        $administratorExtras = $administratorMenu->getExtras();
        unset($administratorExtras['routes']);
        $administratorMenu->setExtras($administratorExtras);

        $administratorViewMenu = $administratorMenu->addChild('administrator_view', ['route' => 'admin_administrator_list', 'label' => t('Administrators overview')]);
        $administratorViewMenu->addChild(
            'new',
            ['route' => 'admin_administrator_new', 'label' => t('New administrator'), 'display' => false]
        );
        $administratorViewMenu->addChild(
            'edit',
            ['route' => 'admin_administrator_edit', 'label' => t('Editing administrator'), 'display' => false]
        );

        $administratorRoleGroupMenu = $administratorMenu->addChild('role_groups', ['route' => 'admin_administratorrolegroup_list', 'label' => t('Role Groups')]);

        $administratorRoleGroupMenu->addChild(
            'new',
            ['route' => 'admin_administratorrolegroup_new', 'label' => t('New administrator role group'), 'display' => false]
        );
        $administratorRoleGroupMenu->addChild(
            'edit',
            ['route' => 'admin_administratorrolegroup_edit', 'label' => t('Editing administrator role group'), 'display' => false]
        );
        $administratorRoleGroupMenu->addChild(
            'copy',
            ['route' => 'admin_administratorrolegroup_copy', 'label' => t('Copy administrator role group'), 'display' => false]
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
                false
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
