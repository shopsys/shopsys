<?php

declare(strict_types=1);

namespace App\Controller\Admin;

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
            ConfigureMenuEvent::SIDE_MENU_PRICING => 'configurePricingMenu',
            ConfigureMenuEvent::SIDE_MENU_PRODUCTS => 'configureProductMenu',
            ConfigureMenuEvent::SIDE_MENU_DASHBOARD => 'configureDashboardMenu',
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'configureSettingsMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureDashboardMenu(ConfigureMenuEvent $event)
    {
        $dashboardMenu = $event->getMenu();
        $dashboardMenu->addChild('transferList', ['route' => 'admin_transfer_list', 'display' => false, 'label' => t('Přehled problémů v přenosech')]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configurePricingMenu(ConfigureMenuEvent $event): void
    {
        $pricingMenu = $event->getMenu();
        $pricingMenu->removeChild('promo_codes');
        $pricingMenu->removeChild('free_transport_and_payment');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureMarketingMenu(ConfigureMenuEvent $event): void
    {
        $marketingMenu = $event->getMenu();

        $blogMenu = $marketingMenu->addChild('blog', ['label' => t('Blog')]);

        $blogCategories = $blogMenu->addChild('blogCategories', ['route' => 'admin_blogcategory_list', 'label' => t('Rubriky blogu')]);
        $blogCategories->addChild('newBlogCategories', ['route' => 'admin_blogcategory_new', 'display' => false, 'label' => t('Nová rubrika blogu')]);
        $blogCategories->addChild('editBlogCategories', ['route' => 'admin_blogcategory_edit', 'display' => false]);

        $blogArticles = $blogMenu->addChild('blogArticles', ['route' => 'admin_blogarticle_list', 'label' => t('Články blogu')]);
        $blogArticles->addChild('newBlogArticles', ['route' => 'admin_blogarticle_new', 'display' => false, 'label' => t('Nový článek blogu')]);
        $blogArticles->addChild('editBlogArticles', ['route' => 'admin_blogarticle_edit', 'display' => false]);

        $promoCodeMenu = $marketingMenu->addChild('promo_codes', ['route' => 'admin_promocode_list', 'label' => t('Slevové kupóny')]);
        $promoCodeMenu->addChild('promo_codes_new', ['route' => 'admin_promocode_new', 'display' => false, 'label' => t('Nový slevový kupóny')]);
        $promoCodeMenu->addChild('promo_codes_edit', ['route' => 'admin_promocode_edit', 'display' => false, 'label' => t('Editace slevového kupónu')]);
        $promoCodeMenu->addChild('promo_codes_newmassgenerate', ['route' => 'admin_promocode_newmassgenerate', 'label' => t('Hromadné vytvoření slevových kupónů'), 'display' => false]);

        $productSeriesMenu = $marketingMenu->addChild('product_series', ['label' => t('Programy produktů')]);

        $productSeriesListMenu = $productSeriesMenu->addChild('product_series_list', ['route' => 'admin_productseries_list', 'label' => t('Programy produktů')]);
        $productSeriesListMenu->addChild('new_product_series', ['route' => 'admin_productseries_new', 'display' => false, 'label' => t('Nový produktový program')]);
        $productSeriesListMenu->addChild('edit_product_series', ['route' => 'admin_productseries_edit', 'display' => false, 'label' => t('Detail produktového programu')]);

        $productSeriesCategoryMenu = $productSeriesMenu->addChild('product_series_category', ['route' => 'admin_productseriescategory_list', 'label' => t('Kategorie')]);
        $productSeriesCategoryMenu->addChild('new_product_series_category', ['route' => 'admin_productseriescategory_new', 'display' => false, 'label' => t('Nová kategorie')]);
        $productSeriesCategoryMenu->addChild('edit_product_series_category', ['route' => 'admin_productseriescategory_edit', 'display' => false, 'label' => t('Detail kategorie')]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureProductMenu(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();

        $stockMenu = $menu->addChild('stock', ['route' => 'admin_stock_list', 'label' => t('Skladovost')]);
        $stockMenu->addChild('new_stock', ['route' => 'admin_stock_new', 'display' => false, 'label' => t('Nový sklad')]);
        $stockMenu->addChild('edit_stock', ['route' => 'admin_stock_edit', 'display' => false, 'label' => t('Detail skladu')]);

        $horizontalMenuMenu = $menu->addChild('horizontal_menu', ['route' => 'admin_horizontalmenu_list', 'label' => t('Horizontální menu')]);
        $horizontalMenuMenu->addChild('horizontal_menu_edit', ['route' => 'admin_horizontalmenu_edit', 'display' => false, 'label' => t('Editace položky')]);
        $horizontalMenuMenu->addChild('horizontal_menu_new', ['route' => 'admin_horizontalmenu_new', 'display' => false, 'label' => t('Nová položka')]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureSettingsMenu(ConfigureMenuEvent $event): void
    {
        $settingsMenu = $event->getMenu();

        $seoMenu = $settingsMenu->getChild('seo');
        $categorySeoMenu = $seoMenu->addChild('categorySeo', ['route' => 'admin_categoryseo_list', 'label' => t('Rozšířené SEO kategorií')]);
        $categorySeoMenu->addChild('new_category', ['route' => 'admin_categoryseo_newcategory', 'label' => t('Rozšířené SEO kategorií - volba kategorie'), 'display' => false]);
        $categorySeoMenu->addChild('new_filters', ['route' => 'admin_categoryseo_newfilters', 'label' => t('Rozšířené SEO kategorie - filtry'), 'display' => false]);
        $categorySeoMenu->addChild('new_combinations', ['route' => 'admin_categoryseo_newcombinations', 'label' => t('Rozšířené SEO kategorie - kombinace'), 'display' => false]);
        $categorySeoMenu->addChild('new_combination', ['route' => 'admin_categoryseo_readycombination', 'label' => t('Rozšířené SEO kategorie - nastavení kombinace se SEO hodnotami'), 'display' => false]);

        $listMenu = $settingsMenu->getChild('lists');
        $listMenu->removeChild('availabilities');

        $productTypeMenu = $listMenu->addChild('product_types', ['route' => 'admin_producttype_list', 'label' => t('Typy produktů')]);
        $productTypeMenu->addChild('new_product_type', ['route' => 'admin_producttype_new', 'display' => false, 'label' => t('Nový typ produktu')]);
        $productTypeMenu->addChild('edit_product_type', ['route' => 'admin_producttype_edit', 'display' => false, 'label' => t('Detail typu produktu')]);

        $parameterUnitMenu = $listMenu->addChild('parameter_units', ['route' => 'admin_parameterunit_list', 'label' => t('Měrné jednotky')]);
        $parameterUnitMenu->addChild('parameter_units_edit', ['route' => 'admin_parameterunit_edit', 'display' => false, 'label' => t('Editace položky')]);

        $parameterValueMenu = $listMenu->addChild('parameter_values', ['route' => 'admin_parametervalue_list', 'label' => t('Hodnota parametru typu barva')]);
        $parameterValueMenu->addChild('parameter_values_edit', ['route' => 'admin_parametervalue_edit', 'display' => false, 'label' => t('Editace hodnoty parametru typu barva')]);
    }
}
