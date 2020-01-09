<?php

declare(strict_types=1);


namespace App\Controller\Admin;


use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_MARKETING => 'configureMarketingMenu',
            ConfigureMenuEvent::SIDE_MENU_PRICING => 'configurePricingMenu',
        ];
    }

    public function configurePricingMenu(ConfigureMenuEvent $event): void
    {
        $pricingMenu = $event->getMenu();
        $pricingMenu->removeChild('promo_codes');
    }

    public function configureMarketingMenu(ConfigureMenuEvent $event): void
    {
        $marketingMenu = $event->getMenu();
        $marketingMenu->addChild('promo_codes', ['route' => 'admin_promocode_list', 'label' => t('Slevové kupóny')]);
    }

}