<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SideMenuBuilder
{
    /**
     * @param \Knp\Menu\FactoryInterface $menuFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly FactoryInterface $menuFactory,
        protected readonly Domain $domain,
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('root');

        $menu->addChild($this->createDashboardMenu());
        $menu->addChild($this->createOrdersMenu());
        $menu->addChild($this->createCustomersMenu());
        $menu->addChild($this->createProductsMenu());
        $menu->addChild($this->createPricingMenu());
        $menu->addChild($this->createMarketingMenu());
        $menu->addChild($this->createAdministratorsMenu());
        $menu->addChild($this->createSettingsMenu());

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ROOT, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createDashboardMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            'dashboard',
            [
                'route' => 'admin_default_dashboard',
                'label' => t('Dashboard'),
            ],
        );
        $menu->setExtra('icon', 'house');

        $menu->addChild('detail', [
            'route' => 'admin_default_crondetail',
            'label' => t('Cron detail'),
            'display' => false,
        ]);
        $menu->addChild('transferIssueList', [
            'route' => 'admin_transferissue_list',
            'display' => false,
            'label' => t('Transfer issues overview'),
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_DASHBOARD, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createOrdersMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('orders', [
            'route' => 'admin_order_list',
            'label' => t('Orders'),
        ]);
        $menu->setExtra('icon', 'document-copy');

        $menu->addChild('edit', [
            'route' => 'admin_order_edit',
            'label' => t('Editing order'),
            'display' => false,
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ORDERS, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createCustomersMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            'customers',
            [
                'label' => t('Customers'),
            ],
        );
        $menu->setExtra('icon', 'person-public');

        $menu->addChild('customers_overview', [
            'route' => 'admin_customer_list',
            'label' => t('Customers overview'),
        ]);
        $menu->addChild('new', [
            'route' => 'admin_customer_new',
            'label' => t('New customer'),
            'display' => false,
        ]);
        $menu->addChild(
            'edit',
            [
                'route' => 'admin_customer_edit',
                'label' => t('Editing customer'),
                'display' => false,
            ],
        );
        $menu->addChild('newsletter', [
            'route' => 'admin_newsletter_list',
            'label' => t('Email newsletter'),
        ]);

        $promoCodeMenu = $menu->addChild('promo_codes', [
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

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_CUSTOMERS, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createProductsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('products', ['label' => t('Products')]);
        $menu->setExtra('icon', 'cart');

        $productsMenu = $menu->addChild(
            'products',
            ['route' => 'admin_product_list', 'label' => t('Products overview')],
        );
        $productsMenu->addChild(
            'new',
            ['route' => 'admin_product_new', 'label' => t('New product'), 'display' => false],
        );
        $productsMenu->addChild(
            'edit',
            ['route' => 'admin_product_edit', 'label' => t('Editing product'), 'display' => false],
        );
        $productsMenu->addChild(
            'new_variant',
            ['route' => 'admin_product_createvariant', 'label' => t('Create variant'), 'display' => false],
        );

        $categoriesMenu = $menu->addChild(
            'categories',
            ['route' => 'admin_category_list', 'label' => t('Categories')],
        );
        $categoriesMenu->addChild(
            'new',
            ['route' => 'admin_category_new', 'label' => t('New category'), 'display' => false],
        );
        $categoriesMenu->addChild(
            'edit',
            ['route' => 'admin_category_edit', 'label' => t('Editing category'), 'display' => false],
        );

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_PRODUCTS, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createPricingMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('pricing', ['label' => t('Pricing')]);
        $menu->setExtra('icon', 'tag');

        $menu->addChild('pricing_groups', ['route' => 'admin_pricinggroup_list', 'label' => t('Pricing groups')]);
        $menu->addChild('vat', ['route' => 'admin_vat_list', 'label' => t('VAT')]);
        $menu->addChild(
            'free_transport_and_payment',
            ['route' => 'admin_transportandpayment_freetransportandpaymentlimit', 'label' => t(
                'Free shipping and payment',
            )],
        );

        if ($this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $currenciesMenuItem = $menu->addChild(
                'currencies',
                ['route' => 'admin_currency_list', 'label' => t('Currencies and rounding')],
            );
            $currenciesMenuItem->setExtra('superadmin', true);
        }

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_PRICING, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createMarketingMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('marketing', ['label' => t('Marketing')]);
        $menu->setExtra('icon', 'chart-piece');

        $articlesMenu = $menu->addChild(
            'articles',
            ['route' => 'admin_article_list', 'label' => t('Articles overview')],
        );
        $articlesMenu->addChild(
            'new',
            ['route' => 'admin_article_new', 'label' => t('New article'), 'display' => false],
        );
        $articlesMenu->addChild(
            'edit',
            ['route' => 'admin_article_edit', 'label' => t('Editing article'), 'display' => false],
        );

        $sliderMenu = $menu->addChild('slider', ['route' => 'admin_slider_list', 'label' => t('Slider on main page')]);
        $sliderMenu->addChild(
            'new_page',
            ['route' => 'admin_slider_new', 'label' => t('New page'), 'display' => false],
        );
        $sliderMenu->addChild(
            'edit_page',
            ['route' => 'admin_slider_edit', 'label' => t('Editing page'), 'display' => false],
        );

        $menu->addChild('top_products', ['route' => 'admin_topproduct_list', 'label' => t('Main page products')]);
        $menu->addChild('top_categories', ['route' => 'admin_topcategory_list', 'label' => t('Popular categories')]);

        $advertsMenu = $menu->addChild(
            'adverts',
            ['route' => 'admin_advert_list', 'label' => t('Advertising system')],
        );
        $advertsMenu->addChild(
            'new',
            ['route' => 'admin_advert_new', 'label' => t('New advertising'), 'display' => false],
        );
        $advertsMenu->addChild(
            'edit',
            ['route' => 'admin_advert_edit', 'label' => t('Editing advertising'), 'display' => false],
        );

        $menu->addChild('feeds', ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);

        $bestsellingProductsMenu = $menu->addChild(
            'bestselling_products',
            ['route' => 'admin_bestsellingproduct_list', 'label' => t('Bestsellers')],
        );
        $bestsellingProductsMenu->addChild(
            'edit',
            ['route' => 'admin_bestsellingproduct_detail', 'label' => t('Editing bestseller'), 'display' => false],
        );

        $blogMenu = $menu->addChild('blog', ['label' => t('Blog')]);

        $blogCategories = $blogMenu->addChild('blogCategories', ['route' => 'admin_blogcategory_list', 'label' => t('Blog categories')]);
        $blogCategories->addChild('newBlogCategories', ['route' => 'admin_blogcategory_new', 'display' => false, 'label' => t('New blog category')]);
        $blogCategories->addChild('editBlogCategories', ['route' => 'admin_blogcategory_edit', 'display' => false]);

        $blogArticles = $blogMenu->addChild('blogArticles', ['route' => 'admin_blogarticle_list', 'label' => t('Blog articles')]);
        $blogArticles->addChild('newBlogArticles', ['route' => 'admin_blogarticle_new', 'display' => false, 'label' => t('New blog article')]);
        $blogArticles->addChild('editBlogArticles', ['route' => 'admin_blogarticle_edit', 'display' => false]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_MARKETING, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createAdministratorsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            'administrators',
            ['route' => 'admin_administrator_list', 'label' => t('Administrators')],
        );
        $menu->setExtra('icon', 'person-door-man');

        $menu->addChild(
            'new',
            ['route' => 'admin_administrator_new', 'label' => t('New administrator'), 'display' => false],
        );
        $menu->addChild(
            'edit',
            ['route' => 'admin_administrator_edit', 'label' => t('Editing administrator'), 'display' => false],
        );

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ADMINISTRATORS, $menu);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    protected function createSettingsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem('settings', ['label' => t('Settings')]);
        $menu->setExtra('icon', 'gear');

        $identificationMenu = $menu->addChild('identification', ['label' => t('E-shop identification')]);

        if ($this->domain->isMultidomain()) {
            $domainsMenu = $identificationMenu->addChild(
                'domains',
                ['route' => 'admin_domain_list', 'label' => t('E-shop identification')],
            );
            $domainsMenu->addChild(
                'edit',
                ['route' => 'admin_domain_edit', 'label' => t('Editing domain'), 'display' => false],
            );
        }
        $identificationMenu->addChild(
            'shop_info',
            ['route' => 'admin_shopinfo_setting', 'label' => t('Operator information')],
        );

        $legalMenu = $menu->addChild('legal', ['label' => t('Legal conditions')]);
        $legalMenu->addChild(
            'legal_conditions',
            ['route' => 'admin_legalconditions_setting', 'label' => t('Legal conditions')],
        );
        $legalMenu->addChild(
            'personal_data',
            ['route' => 'admin_personaldata_setting', 'label' => t('Personal data access')],
        );
        $legalMenu->addChild('cookies', ['route' => 'admin_cookies_setting', 'label' => t('Cookies information')]);

        $communicationMenu = $menu->addChild('communication', ['label' => t('Communication with customer')]);
        $communicationMenu->addChild(
            'mail_settings',
            ['route' => 'admin_mail_setting', 'label' => t('Email settings')],
        );
        $mailTemplates = $communicationMenu->addChild(
            'mail_templates',
            ['route' => 'admin_mail_template', 'label' => t('Email templates')],
        );
        $mailTemplates->addChild(
            'edit_template',
            ['route' => 'admin_mail_edit', 'label' => t('Editing email template'), 'display' => false],
        );
        $communicationMenu->addChild(
            'order_confirmation',
            ['route' => 'admin_customercommunication_ordersubmitted', 'label' => t('Order confirmation page')],
        );

        $listsMenu = $menu->addChild('lists', ['label' => t('Lists and nomenclatures')]);
        $transportsAndPaymentsMenu = $listsMenu->addChild(
            'transports_and_payments',
            ['route' => 'admin_transportandpayment_list', 'label' => t('Shippings and payments')],
        );
        $transportsAndPaymentsMenu->addChild(
            'new_transport',
            ['route' => 'admin_transport_new', 'label' => t('New shipping'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            'edit_transport',
            ['route' => 'admin_transport_edit', 'label' => t('Editing shipping'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            'new_payment',
            ['route' => 'admin_payment_new', 'label' => t('New payment'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            'edit_payment',
            ['route' => 'admin_payment_edit', 'label' => t('Editing payment'), 'display' => false],
        );
        $listsMenu->addChild('flags', ['route' => 'admin_flag_list', 'label' => t('Flags')]);
        $listsMenu->addChild('parameters', ['route' => 'admin_parameter_list', 'label' => t('Parameters')]);
        $listsMenu->addChild(
            'order_statuses',
            ['route' => 'admin_orderstatus_list', 'label' => t('Status of orders')],
        );
        $brandsMenu = $listsMenu->addChild('brands', ['route' => 'admin_brand_list', 'label' => t('Brands')]);
        $brandsMenu->addChild('new', ['route' => 'admin_brand_new', 'label' => t('New brand'), 'display' => false]);
        $brandsMenu->addChild(
            'edit',
            ['route' => 'admin_brand_edit', 'label' => t('Editing brand'), 'display' => false],
        );
        $listsMenu->addChild('units', ['route' => 'admin_unit_list', 'label' => t('Units')]);
        $countriesMenu = $listsMenu->addChild(
            'countries',
            ['route' => 'admin_country_list', 'label' => t('Countries')],
        );
        $countriesMenu->addChild(
            'new',
            ['route' => 'admin_country_new', 'label' => t('New country'), 'display' => false],
        );
        $countriesMenu->addChild(
            'edit',
            ['route' => 'admin_country_edit', 'label' => t('Editing country'), 'display' => false],
        );

        $seoMenu = $menu->addChild('seo', ['label' => t('SEO')]);
        $seoMenu->addChild('seo', ['route' => 'admin_seo_index', 'label' => t('SEO')]);
        $seoMenu->addChild('robots', ['route' => 'admin_seo_robots', 'label' => t('Robots.txt')]);
        $seoMenu->addChild('hreflang', ['route' => 'admin_seo_hreflang', 'label' => t('Alternate language settings')]);

        $seoPageMenu = $seoMenu->addChild('seoPageList', ['route' => 'admin_seopage_list', 'label' => t('SEO pages')]);
        $seoPageMenu->addChild('seoPageNew', ['route' => 'admin_seopage_new', 'label' => t('New SEO page'), 'display' => false]);
        $seoPageMenu->addChild('seoPageEdit', ['route' => 'admin_seopage_edit', 'label' => t('Editing SEO page'), 'display' => false]);

        $contactFormSettingsMenu = $menu->addChild('contact_form_settings', ['label' => t('Contact form')]);
        $contactFormSettingsMenu->addChild(
            'contact_form_settings',
            ['route' => 'admin_contactformsettings_index', 'label' => t('Contact form')],
        );

        if ($this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)) {
            $superadminMenu = $menu->addChild('superadmin', ['label' => t('Superadmin')]);
            $superadminMenu->setExtra('superadmin', true);
            $superadminMenu->addChild('modules', ['route' => 'admin_superadmin_modules', 'label' => t('Modules')]);
            $superadminMenu->addChild(
                'errors',
                ['route' => 'admin_superadmin_errors', 'label' => t('Error messages')],
            );
            $superadminMenu->addChild(
                'pricing',
                ['route' => 'admin_superadmin_pricing', 'label' => t('Sales including/excluding VAT settings')],
            );
            $superadminMenu->addChild(
                'css_docs',
                ['route' => 'admin_superadmin_cssdocumentation', 'label' => t('CSS documentation')],
            );
            $superadminMenu->addChild('urls', ['route' => 'admin_superadmin_urls', 'label' => t('URL addresses')]);
            $superadminMenu->addChild(
                'mail_whitelist',
                ['route' => 'admin_superadmin_mailwhitelist', 'label' => t('E-mail whitelist settings')],
            );
        }

        $heurekaMenu = $menu->addChild('heureka', ['label' => t('Heureka - Verified by Customer')]);
        $heurekaMenu->addChild(
            'settings',
            ['route' => 'admin_heureka_setting', 'label' => t('Heureka - Verified by Customer')],
        );

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_SETTINGS, $menu);

        return $menu;
    }

    /**
     * @param string $eventName
     * @param \Knp\Menu\ItemInterface $menu
     * @return \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent
     */
    protected function dispatchConfigureMenuEvent(string $eventName, ItemInterface $menu): ConfigureMenuEvent
    {
        $event = new ConfigureMenuEvent($this->menuFactory, $menu);

        /** @var \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $configureMenuEvent */
        $configureMenuEvent = $this->eventDispatcher->dispatch($event, $eventName);

        return $configureMenuEvent;
    }
}
