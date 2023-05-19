<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConfigureMenuEvent extends Event
{
    public const SIDE_MENU_ROOT = 'shopsys.admin_side_menu.configure_root';
    public const SIDE_MENU_DASHBOARD = 'shopsys.admin_side_menu.configure_dashboard';
    public const SIDE_MENU_ORDERS = 'shopsys.admin_side_menu.configure_orders';
    public const SIDE_MENU_CUSTOMERS = 'shopsys.admin_side_menu.configure_customers';
    public const SIDE_MENU_PRODUCTS = 'shopsys.admin_side_menu.configure_products';
    public const SIDE_MENU_PRICING = 'shopsys.admin_side_menu.configure_pricing';
    public const SIDE_MENU_MARKETING = 'shopsys.admin_side_menu.configure_marketing';
    public const SIDE_MENU_ADMINISTRATORS = 'shopsys.admin_side_menu.configure_administrators';
    public const SIDE_MENU_SETTINGS = 'shopsys.admin_side_menu.configure_settings';

    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     * @param \Knp\Menu\ItemInterface $menu
     */
    public function __construct(protected readonly FactoryInterface $menuFactory, protected readonly ItemInterface $menu)
    {
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getMenuFactory(): FactoryInterface
    {
        return $this->menuFactory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }
}
