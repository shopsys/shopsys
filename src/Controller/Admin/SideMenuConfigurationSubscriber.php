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
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureMarketingMenu(ConfigureMenuEvent $event): void
    {
        $marketingMenu = $event->getMenu();

        $promoCodeMenu = $marketingMenu->addChild('promo_codes', ['route' => 'admin_promocode_list', 'label' => t('Slevové kupóny')]);
        $promoCodeMenu->addChild('promo_codes_new', ['route' => 'admin_promocode_new', 'display' => false, 'label' => t('Nový slevový kupóny')]);
        $promoCodeMenu->addChild('promo_codes_edit', ['route' => 'admin_promocode_edit', 'display' => false, 'label' => t('Editace slevového kupónu')]);

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
        $productMenu = $event->getMenu();
        $productMenu->addChild('stock', ['route' => 'admin_stock_list', 'label' => t('Skladovost')]);

        $stockMenu = $productMenu->getChild('stock');
        $stockMenu->addChild('new_stock', ['route' => 'admin_stock_new', 'display' => false, 'label' => t('Nový sklad')]);
        $stockMenu->addChild('edit_stock', ['route' => 'admin_stock_edit', 'display' => false, 'label' => t('Detail skladu')]);
    }
}
