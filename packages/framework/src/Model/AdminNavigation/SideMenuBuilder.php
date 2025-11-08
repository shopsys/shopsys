<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SideMenuBuilder
{
    public const string ROOT = 'root';
    public const string ROOT_DASHBOARD = 'dashboard';
    public const string DETAIL_CRON = 'detail';
    public const string LIST_TRANSFER_ISSUE = 'transferIssueList';
    public const string LIST_ORDER = 'orders';
    public const string EDIT_ORDER = 'edit';
    public const string LIST_INQUIRY = 'inquiries';
    public const string DETAIL_INQUIRY = 'detail';
    public const string ROOT_CUSTOMER = 'customers';
    public const string LIST_CUSTOMER = 'customers_overview';
    public const string NEW_CUSTOMER = 'new';
    public const string EDIT_CUSTOMER = 'edit';
    public const string EDIT_BILLING_ADDRESS = 'billingAddressEdit';
    public const string EDIT_CUSTOMER_USER = 'customerUserEdit';
    public const string NEW_CUSTOMER_USER = 'customerUserNew';
    public const string EDIT_DELIVERY_ADDRESS = 'deliverAddressEdit';
    public const string NEW_DELIVER_ADDRESS = 'deliverAddressNew';
    public const string LIST_SALES_REPRESENTATIVE = 'salesRepresentatives';
    public const string NEW_SALES_REPRESENTATIVE = 'salesRepresentativeNew';
    public const string EDIT_SALES_REPRESENTATIVE = 'salesRepresentativeEdit';
    public const string LIST_NEWSLETTER = 'newsletter';
    public const string LIST_PROMO_CODE = 'promo_codes';
    public const string LIST_PROMO_CODE_BATCH = 'admin_promocode_listmassgeneratebatch';
    public const string NEW_PROMO_CODE = 'promo_codes_new';
    public const string EDIT_PROMO_CODE = 'promo_codes_edit';
    public const string GENERATE_PROMO_CODE_BATCH = 'promo_codes_newmassgenerate';
    public const string LIST_CUSTOMER_USER_ROLE_GROUP = 'customer_user_role_group';
    public const string NEW_CUSTOMER_USER_ROLE_GROUP = 'admin_superadmin_customer_user_role_group_new';
    public const string EDIT_CUSTOMER_USER_ROLE_GROUP = 'admin_superadmin_customer_user_role_group_edit';
    public const string LIST_COMPLAINT = 'complaints';
    public const string EDIT_COMPLAINT = 'admin_complaint_edit';
    public const string LIST_WATCHDOG = 'watchdogs';
    public const string DETAIL_WATCHDOG = 'watchdog_detail';
    public const string ROOT_PRODUCT = 'products';
    public const string LIST_PRODUCT = 'products';
    public const string NEW_PRODUCT = 'new';
    public const string EDIT_PRODUCT = 'edit';
    public const string NEW_VARIANT = 'new_variant';
    public const string LIST_CATEGORY = 'categories';
    public const string NEW_CATEGORY = 'new';
    public const string EDIT_CATEGORY = 'edit';
    public const string ROOT_PRICING = 'pricing';
    public const string LIST_PRICE_LIST = 'price_list';
    public const string NEW_PRICE_LIST = 'new';
    public const string EDIT_PRICE_LIST = 'edit';
    public const string IMPORT_PRICE_LIST = 'import';
    public const string LIST_PRICING_GROUP = 'pricing_groups';
    public const string LIST_VAT = 'vat';
    public const string FREE_TRANSPORT_AND_PAYMENT = 'free_transport_and_payment';
    public const string GIFT_PLAN_LIST = 'gift_plan_list';
    public const string GIFT_PLAN_NEW = 'gift_plan_new';
    public const string GIFT_PLAN_EDIT = 'gift_plan_edit';
    public const string LIST_CURRENCY = 'currencies';
    public const string ROOT_CMS = 'marketing';
    public const string LIST_ARTICLE = 'articles';
    public const string NEW_ARTICLE = 'new';
    public const string EDIT_ARTICLE = 'edit';
    public const string LIST_ADVERT = 'adverts';
    public const string NEW_ADVERT = 'new';
    public const string EDIT_ADVERT = 'edit';
    public const string LIST_BESTSELLING_PRODUCT = 'bestselling_products';
    public const string DETAIL_BESTSELLING_PRODUCT = 'edit';
    public const string SECTION_BLOG = 'blog';
    public const string LIST_BLOG_CATEGORY = 'blogCategories';
    public const string NEW_BLOG_CATEGORY = 'newBlogCategories';
    public const string EDIT_BLOG_CATEGORY = 'editBlogCategories';
    public const string LIST_BLOG_ARTICLE = 'blogArticles';
    public const string NEW_BLOG_ARTICLE = 'newBlogArticles';
    public const string EDIT_BLOG_ARTICLE = 'editBlogArticles';
    public const string LIST_NAVIGATION = 'navigation';
    public const string EDIT_NAVIGATION = 'navigation_edit';
    public const string NEW_NAVIGATION = 'navigation_new';
    public const string SECTION_HOMEPAGE = 'homepage';
    public const string LIST_BANNER = 'banners';
    public const string NEW_BANNER = 'new_page';
    public const string EDIT_BANNER = 'edit_page';
    public const string LIST_PROMOTED_PRODUCT = 'promoted_products';
    public const string LIST_PROMOTED_CATEGORY = 'promoted_categories';
    public const string LIST_NOTIFICATION_BAR = 'notification_bar';
    public const string NEW_NOTIFICATION_BAR = 'notification_bar_new';
    public const string EDIT_NOTIFICATION_BAR = 'notification_bar_edit';
    public const string ORDER_CONFIRMATION = 'order_confirmation';
    public const string SECTION_LEGAL = 'legal';
    public const string TERMS_AND_CONDITIONS = 'terms_and_conditions';
    public const string PRIVACY_POLICY = 'privacy_policy';
    public const string PERSONAL_DATA = 'personal_data';
    public const string USER_CONSENT_POLICY = 'user_consent_policy';
    public const string AUTOCOMPLETE_SETTING = 'autocomplete';
    public const string ROOT_FILE = 'files';
    public const string LIST_FILE = 'files';
    public const string EDIT_FILE = 'edit';
    public const string NEW_FILE = 'new';
    public const string ROOT_ADMINISTRATOR = 'administrators';
    public const string LIST_ADMINISTRATOR = 'administrators_overview';
    public const string NEW_ADMINISTRATOR = 'new';
    public const string EDIT_ADMINISTRATOR = 'edit';
    public const string ENABLE_TWO_FACTOR_AUTHENTICATION = 'enable-two-factor-authentication';
    public const string DISABLE_TWO_FACTOR_AUTHENTICATION = 'disable-two-factor-authentication';
    public const string LIST_ADMINISTRATOR_ROLE_GROUP = 'role_groups';
    public const string NEW_ADMINISTRATOR_ROLE_GROUP = 'new';
    public const string EDIT_ADMINISTRATOR_ROLE_GROUP = 'edit';
    public const string COPY_ADMINISTRATOR_ROLE_GROUP = 'copy';
    public const string ROOT_SETTING = 'settings';
    public const string SECTION_IDENTIFICATION = 'identification';
    public const string LIST_DOMAIN = 'domains';
    public const string EDIT_DOMAIN = 'edit';
    public const string SECTION_COMMUNICATION = 'communication';
    public const string MAIL_SETTING = 'mail_settings';
    public const string LIST_MAIL_TEMPLATE = 'mail_templates';
    public const string EDIT_MAIL_TEMPLATE = 'edit_template';
    public const string SECTION_LISTS = 'lists';
    public const string LIST_TRANSPORT_AND_PAYMENT = 'transports_and_payments';
    public const string NEW_TRANSPORT = 'new_transport';
    public const string EDIT_TRANSPORT = 'edit_transport';
    public const string NEW_PAYMENT = 'new_payment';
    public const string EDIT_PAYMENT = 'edit_payment';
    public const string LIST_FLAG = 'flags';
    public const string NEW_FLAG = 'flagNew';
    public const string EDIT_FLAG = 'flagEdit';
    public const string LIST_PARAMETER = 'parameters';
    public const string NEW_PARAMETER = 'parameters_new';
    public const string EDIT_PARAMETER = 'parameters_edit';
    public const string EDIT_PARAMETERS_VALUE = 'parameters_values_edit';
    public const string LIST_PARAMETER_GROUP = 'parametergroups';
    public const string NEW_PARAMETER_GROUP = 'parametergroups_new';
    public const string EDIT_PARAMETER_GROUP = 'parametergroups_edit';
    public const string LIST_ORDER_STATUS = 'order_statuses';
    public const string LIST_COMPLAINT_STATUS = 'complaint_statuses';
    public const string LIST_BRAND = 'brands';
    public const string NEW_BRAND = 'new';
    public const string EDIT_BRAND = 'edit';
    public const string LIST_UNIT = 'units';
    public const string LIST_COUNTRY = 'countries';
    public const string NEW_COUNTRY = 'new';
    public const string EDIT_COUNTRY = 'edit';
    public const string LIST_PARAMETER_VALUE = 'parameter_values';
    public const string EDIT_PARAMETER_VALUE = 'parameter_values_edit';
    public const string LIST_STORE = 'stores';
    public const string NEW_STORE = 'new_store';
    public const string EDIT_STORE = 'edit_store';
    public const string LIST_CLOSED_DAY = 'closed_day';
    public const string NEW_CLOSED_DAY = 'closed_day_new';
    public const string EDIT_CLOSED_DAY = 'closed_day_edit';
    public const string HOLIDAYS_IMPORT = 'closed_day_holidaysimport';
    public const string SECTION_SEO = 'seo';
    public const string SEO = 'seo';
    public const string ROBOTS = 'robots';
    public const string HREFLANG = 'hreflang';
    public const string LIST_UNUSED_FRIENDLY_URL = 'unusedFriendlyUrlList';
    public const string LIST_SEO_PAGE = 'seoPageList';
    public const string NEW_SEO_PAGE = 'seoPageNew';
    public const string EDIT_SEO_PAGE = 'seoPageEdit';
    public const string LIST_CATEGORY_SEO = 'categorySeo';
    public const string NEW_CATEGORY_SEO = 'new_category';
    public const string NEW_CATEGORY_SEO_FILTERS = 'new_filters';
    public const string NEW_CATEGORY_SEO_COMBINATIONS = 'new_combinations';
    public const string NEW_CATEGORY_SEO_COMBINATION = 'new_combination';
    public const string SECTION_CONTACT_FORM = 'contact_form_settings';
    public const string CONTACT_FORM_SETTINGS = 'contact_form_settings';
    public const string SECTION_STOCKS = 'stocks';
    public const string LIST_STOCK = 'stock';
    public const string NEW_STOCK = 'new_stock';
    public const string EDIT_STOCK = 'edit_stock';
    public const string STOCK_SETTINGS = 'stock_settings';
    public const string SECTION_CONSTANTS = 'constants';
    public const string LIST_CONSTANT = 'constants_list';
    public const string EDIT_CONSTANT = 'constants_edit';
    public const string SECTION_SUPERADMIN = 'superadmin';
    public const string LIST_MODULE = 'modules';
    public const string PRICING = 'pricing';
    public const string LIST_URL = 'urls';
    public const string MAIL_WHITELIST = 'mail_whitelist';
    public const string CLEAR_STOREFRONT_CACHE = 'clear_storefront_cache';
    public const string CSP_HEADER = 'cspHeader';
    public const string ROOT_INTEGRATIONS = 'integrations';
    public const string LIST_FEED = 'feeds';
    public const string MASTRA_DASHBOARD = 'mastra_dashboard';
    public const string MASTRA_SQL_DASHBOARD = 'mastra_sql_dashboard';
    public const string SECTION_HEUREKA = 'heureka';
    public const string HEUREKA_SETTINGS = 'settings';
    public const string MAIL_ALLOWED_RECIPIENTS = 'mail_whitelist_overview';

    public function __construct(
        protected readonly FactoryInterface $menuFactory,
        protected readonly Domain $domain,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::ROOT);

        $menu->addChild($this->createDashboardMenu());
        $menu->addChild($this->createMailAllowedRecipientsMenu());
        $menu->addChild($this->createOrdersMenu());
        $menu->addChild($this->createInquiriesMenu());
        $menu->addChild($this->createCustomersMenu());
        $menu->addChild($this->createProductsMenu());
        $menu->addChild($this->createPricingMenu());
        $menu->addChild($this->createMarketingMenu());
        $menu->addChild($this->createFilesMenu());
        $menu->addChild($this->createAdministratorsMenu());
        $menu->addChild($this->createSettingsMenu());
        $menu->addChild($this->createIntegrationsMenu());

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ROOT, $menu);

        return $menu;
    }

    protected function createDashboardMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            static::ROOT_DASHBOARD,
            [
                'route' => 'admin_default_dashboard',
                'label' => t('Dashboard'),
            ],
        );
        $menu->setExtra('icon', 'house');

        $menu->addChild(static::DETAIL_CRON, [
            'route' => 'admin_default_crondetail',
            'label' => t('Cron detail'),
            'display' => false,
        ]);
        $menu->addChild(static::LIST_TRANSFER_ISSUE, [
            'route' => 'admin_transferissue_list',
            'display' => false,
            'label' => t('Transfer issues overview'),
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_DASHBOARD, $menu);

        return $menu;
    }

    public function createMailAllowedRecipientsMenu(): ItemInterface
    {
        return $this->menuFactory->createItem(
            static::MAIL_ALLOWED_RECIPIENTS,
            [
                'route' => 'admin_mailallowedrecipient_list',
                'label' => t('Email allowed recipients (whitelist)'),
                'display' => false,
            ],
        );
    }

    protected function createOrdersMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::LIST_ORDER, [
            'route' => 'admin_order_list',
            'label' => t('Orders'),
        ]);
        $menu->setExtra('icon', 'document-copy');

        $menu->addChild(static::EDIT_ORDER, [
            'route' => 'admin_order_edit',
            'label' => t('Editing order'),
            'display' => false,
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ORDERS, $menu);

        return $menu;
    }

    protected function createInquiriesMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::LIST_INQUIRY, [
            'route' => 'admin_inquiry_list',
            'label' => t('Inquiries'),
        ]);
        $menu->setExtra('icon', 'letter');

        $menu->addChild(static::DETAIL_INQUIRY, [
            'route' => 'admin_inquiry_detail',
            'label' => t('Inquiry detail'),
            'display' => false,
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_INQUIRIES, $menu);

        return $menu;
    }

    protected function createCustomersMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            static::ROOT_CUSTOMER,
            [
                'label' => t('Customers'),
            ],
        );
        $menu->setExtra('icon', 'person-public');

        $customersOverview = $menu->addChild('' . static::LIST_CUSTOMER, [
            'route' => 'admin_customer_list',
            'label' => t('Customers overview'),
        ]);
        $customersOverview->addChild(static::NEW_CUSTOMER, [
            'route' => 'admin_customer_new',
            'label' => t('New customer'),
            'display' => false,
        ]);
        $customerEdit = $customersOverview->addChild(
            static::EDIT_CUSTOMER,
            [
                'route' => 'admin_customer_edit',
                'label' => t('Editing customer'),
                'display' => false,
            ],
        );

        $customerEdit->addChild(static::EDIT_BILLING_ADDRESS, [
            'route' => 'admin_billing_address_edit',
            'display' => false,
        ]);

        $customerEdit->addChild(static::EDIT_CUSTOMER_USER, [
            'route' => 'admin_customer_user_edit',
            'display' => false,
        ]);
        $customerEdit->addChild(static::NEW_CUSTOMER_USER, [
            'route' => 'admin_customer_new_customer_user',
            'label' => t('Add customer user'),
            'display' => false,
        ]);

        $customerEdit->addChild(static::EDIT_DELIVERY_ADDRESS, [
            'route' => 'admin_delivery_address_edit',
            'display' => false,
        ]);
        $customerEdit->addChild(static::NEW_DELIVER_ADDRESS, [
            'route' => 'admin_delivery_address_new',
            'label' => t('New delivery address'),
            'display' => false,
        ]);

        $salesRepresentativeMenu = $menu->addChild(static::LIST_SALES_REPRESENTATIVE, [
            'route' => 'admin_salesrepresentative_list',
            'label' => t('Sales representatives'),
        ]);
        $salesRepresentativeMenu->addChild(static::NEW_SALES_REPRESENTATIVE, [
            'route' => 'admin_salesrepresentative_new',
            'label' => t('New sales representative'),
            'display' => false,
        ]);
        $salesRepresentativeMenu->addChild(static::EDIT_SALES_REPRESENTATIVE, [
            'route' => 'admin_salesrepresentative_edit',
            'display' => false,
            'label' => t('Editing sales representative'),
        ]);

        $menu->addChild(static::LIST_NEWSLETTER, [
            'route' => 'admin_newsletter_list',
            'label' => t('Email newsletter'),
        ]);

        $promoCodeMenu = $menu->addChild(static::LIST_PROMO_CODE, [
            'route' => 'admin_promocode_list',
            'label' => t('Promo codes'),
        ]);
        $promoCodeMenu->addChild(static::LIST_PROMO_CODE_BATCH, [
            'route' => 'admin_promocode_listmassgeneratebatch',
            'display' => true,
            'label' => t('Generated batches'),
        ]);
        $promoCodeMenu->addChild(static::NEW_PROMO_CODE, [
            'route' => 'admin_promocode_new',
            'display' => false,
            'label' => t('New promo code'),
        ]);
        $promoCodeMenu->addChild(static::EDIT_PROMO_CODE, [
            'route' => 'admin_promocode_edit',
            'display' => false,
            'label' => t('Editing promo code'),
        ]);
        $promoCodeMenu->addChild(static::GENERATE_PROMO_CODE_BATCH, [
            'route' => 'admin_promocode_newmassgenerate',
            'label' => t('Bulk creation of promo codes'),
            'display' => false,
        ]);

        $roleGroupMenu = $menu->addChild(static::LIST_CUSTOMER_USER_ROLE_GROUP, [
            'route' => 'admin_superadmin_customer_user_role_group_list',
            'label' => t('Customer user role groups'),
        ]);

        $roleGroupMenu->setExtra('superadmin', true);

        $roleGroupMenu->addChild(static::NEW_CUSTOMER_USER_ROLE_GROUP, [
            'route' => 'admin_superadmin_customer_user_role_group_new',
            'display' => false,
            'label' => t('New customer user role group'),
        ]);
        $roleGroupMenu->addChild(static::EDIT_CUSTOMER_USER_ROLE_GROUP, [
            'route' => 'admin_superadmin_customer_user_role_group_edit',
            'display' => false,
            'label' => t('Editing customer user role group'),
        ]);

        $complaintsMenu = $menu->addChild(static::LIST_COMPLAINT, [
            'route' => 'admin_complaint_list',
            'label' => t('Complaints'),
        ]);

        $complaintsMenu->addChild(
            static::EDIT_COMPLAINT,
            [
                'route' => 'admin_complaint_edit',
                'label' => t('Editing complaint'),
                'display' => false,
            ],
        );

        $watchdogMenu = $menu->addChild(static::LIST_WATCHDOG, [
            'route' => 'admin_watchdog_list',
            'display' => true,
            'label' => t('Watchdogs'),
        ]);
        $watchdogMenu->addChild(static::DETAIL_WATCHDOG, [
            'route' => 'admin_watchdog_detail',
            'display' => false,
            'label' => t('Watchdog detail'),
        ]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_CUSTOMERS, $menu);

        return $menu;
    }

    protected function createProductsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::ROOT_PRODUCT, ['label' => t('Products')]);
        $menu->setExtra('icon', 'cart');

        $productsMenu = $menu->addChild(
            static::LIST_PRODUCT,
            ['route' => 'admin_product_list', 'label' => t('Products overview')],
        );
        $productsMenu->addChild(
            static::NEW_PRODUCT,
            ['route' => 'admin_product_new', 'label' => t('New product'), 'display' => false],
        );
        $productsMenu->addChild(
            static::EDIT_PRODUCT,
            ['route' => 'admin_product_edit', 'label' => t('Editing product'), 'display' => false],
        );
        $productsMenu->addChild(
            static::NEW_VARIANT,
            ['route' => 'admin_product_createvariant', 'label' => t('Create variant'), 'display' => false],
        );

        $categoriesMenu = $menu->addChild(
            static::LIST_CATEGORY,
            ['route' => 'admin_category_list', 'label' => t('Categories')],
        );
        $categoriesMenu->addChild(
            static::NEW_CATEGORY,
            ['route' => 'admin_category_new', 'label' => t('New category'), 'display' => false],
        );
        $categoriesMenu->addChild(
            static::EDIT_CATEGORY,
            ['route' => 'admin_category_edit', 'label' => t('Editing category'), 'display' => false],
        );

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_PRODUCTS, $menu);

        return $menu;
    }

    protected function createPricingMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::ROOT_PRICING, ['label' => t('Pricing')]);
        $menu->setExtra('icon', 'tag');

        $priceListMenu = $menu->addChild(static::LIST_PRICE_LIST, ['route' => 'admin_pricelist_list', 'label' => t('Price lists')]);
        $priceListMenu->addChild(
            static::NEW_PRICE_LIST,
            ['route' => 'admin_pricelist_new', 'label' => t('New price list'), 'display' => false],
        );
        $priceListMenu->addChild(
            static::EDIT_PRICE_LIST,
            ['route' => 'admin_pricelist_edit', 'label' => t('Editing price list'), 'display' => false],
        );
        $priceListMenu->addChild(
            static::IMPORT_PRICE_LIST,
            ['route' => 'admin_pricelist_import', 'label' => t('Import price list'), 'display' => false],
        );

        $menu->addChild(static::LIST_PRICING_GROUP, ['route' => 'admin_pricinggroup_list', 'label' => t('Pricing groups')]);
        $menu->addChild(static::LIST_VAT, ['route' => 'admin_vat_list', 'label' => t('VAT')]);
        $menu->addChild(
            static::FREE_TRANSPORT_AND_PAYMENT,
            ['route' => 'admin_transportandpayment_freetransportandpaymentlimit', 'label' => t(
                'Free shipping and payment',
            )],
        );

        $currenciesMenuItem = $menu->addChild(
            static::LIST_CURRENCY,
            ['route' => 'admin_currency_list', 'label' => t('Currencies and rounding')],
        );
        $currenciesMenuItem->setExtra('superadmin', true);

        $giftPlansMenu = $menu->addChild(
            static::GIFT_PLAN_LIST,
            ['route' => 'admin_giftplan_list', 'label' => t(
                'Gift plans',
            )],
        );
        $giftPlansMenu->addChild(static::GIFT_PLAN_NEW, ['route' => 'admin_giftplan_new', 'label' => t('New gift plan'), 'display' => false]);
        $giftPlansMenu->addChild(static::GIFT_PLAN_EDIT, ['route' => 'admin_giftplan_edit', 'label' => t('Editing gift plan'), 'display' => false]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_PRICING, $menu);

        return $menu;
    }

    protected function createMarketingMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::ROOT_CMS, ['label' => t('CMS')]);
        $menu->setExtra('icon', 'chart-piece');

        $articlesMenu = $menu->addChild(
            static::LIST_ARTICLE,
            ['route' => 'admin_article_list', 'label' => t('Articles overview')],
        );
        $articlesMenu->addChild(
            static::NEW_ARTICLE,
            ['route' => 'admin_article_new', 'label' => t('New article'), 'display' => false],
        );
        $articlesMenu->addChild(
            static::EDIT_ARTICLE,
            ['route' => 'admin_article_edit', 'label' => t('Editing article'), 'display' => false],
        );

        $advertsMenu = $menu->addChild(
            static::LIST_ADVERT,
            ['route' => 'admin_advert_list', 'label' => t('Advertising system')],
        );
        $advertsMenu->addChild(
            static::NEW_ADVERT,
            ['route' => 'admin_advert_new', 'label' => t('New advertising'), 'display' => false],
        );
        $advertsMenu->addChild(
            static::EDIT_ADVERT,
            ['route' => 'admin_advert_edit', 'label' => t('Editing advertising'), 'display' => false],
        );

        $bestsellingProductsMenu = $menu->addChild(
            static::LIST_BESTSELLING_PRODUCT,
            ['route' => 'admin_bestsellingproduct_list', 'label' => t('Bestsellers')],
        );
        $bestsellingProductsMenu->addChild(
            static::DETAIL_BESTSELLING_PRODUCT,
            ['route' => 'admin_bestsellingproduct_detail', 'label' => t('Editing bestseller'), 'display' => false],
        );

        $blogMenu = $menu->addChild(static::SECTION_BLOG, ['label' => t('Blog')]);

        $blogCategories = $blogMenu->addChild(static::LIST_BLOG_CATEGORY, ['route' => 'admin_blogcategory_list', 'label' => t('Blog categories')]);
        $blogCategories->addChild(static::NEW_BLOG_CATEGORY, ['route' => 'admin_blogcategory_new', 'display' => false, 'label' => t('New blog category')]);
        $blogCategories->addChild(static::EDIT_BLOG_CATEGORY, ['route' => 'admin_blogcategory_edit', 'display' => false]);

        $blogArticles = $blogMenu->addChild(static::LIST_BLOG_ARTICLE, ['route' => 'admin_blogarticle_list', 'label' => t('Blog articles')]);
        $blogArticles->addChild(static::NEW_BLOG_ARTICLE, ['route' => 'admin_blogarticle_new', 'display' => false, 'label' => t('New blog article')]);
        $blogArticles->addChild(static::EDIT_BLOG_ARTICLE, ['route' => 'admin_blogarticle_edit', 'display' => false]);

        $navigationMenu = $menu->addChild(static::LIST_NAVIGATION, ['route' => 'admin_navigation_list', 'label' => t('Navigation')]);
        $navigationMenu->addChild(static::EDIT_NAVIGATION, ['route' => 'admin_navigation_edit', 'display' => false, 'label' => t('Editing item')]);
        $navigationMenu->addChild(static::NEW_NAVIGATION, ['route' => 'admin_navigation_new', 'display' => false, 'label' => t('New item')]);

        $homepageMenu = $menu->addChild(static::SECTION_HOMEPAGE, ['label' => t('Home page')]);

        $bannersMenu = $homepageMenu->addChild(static::LIST_BANNER, ['route' => 'admin_slider_list', 'label' => t('Banners')]);
        $bannersMenu->addChild(static::NEW_BANNER, ['route' => 'admin_slider_new', 'label' => t('New page'), 'display' => false]);
        $bannersMenu->addChild(static::EDIT_BANNER, ['route' => 'admin_slider_edit', 'label' => t('Editing page'), 'display' => false]);

        $homepageMenu->addChild(static::LIST_PROMOTED_PRODUCT, ['route' => 'admin_topproduct_list', 'label' => t('Promoted products')]);
        $homepageMenu->addChild(static::LIST_PROMOTED_CATEGORY, ['route' => 'admin_topcategory_list', 'label' => t('Promoted categories')]);

        $menu->addChild(static::AUTOCOMPLETE_SETTING, ['route' => 'admin_autocomplete_setting', 'label' => t('Autocomplete favorites')]);

        $notificationBarMenu = $menu->addChild(static::LIST_NOTIFICATION_BAR, ['route' => 'admin_notificationbar_list', 'label' => t('Notification bar')]);
        $notificationBarMenu->addChild(static::NEW_NOTIFICATION_BAR, ['route' => 'admin_notificationbar_new', 'label' => t('New notification bar'), 'display' => false]);
        $notificationBarMenu->addChild(static::EDIT_NOTIFICATION_BAR, ['route' => 'admin_notificationbar_edit', 'label' => t('Editing notification bar'), 'display' => false]);

        $menu->addChild(static::ORDER_CONFIRMATION, ['route' => 'admin_customercommunication_ordersubmitted', 'label' => t('Order confirmation page')]);

        $legalMenu = $menu->addChild(static::SECTION_LEGAL, ['label' => t('Legal conditions')]);
        $legalMenu->addChild(
            static::TERMS_AND_CONDITIONS,
            ['route' => 'admin_legalconditions_termsandconditions', 'label' => t('Terms and conditions')],
        );
        $legalMenu->addChild(
            static::PRIVACY_POLICY,
            ['route' => 'admin_legalconditions_privacypolicy', 'label' => t('Privacy policy')],
        );
        $legalMenu->addChild(
            static::PERSONAL_DATA,
            ['route' => 'admin_personaldata_setting', 'label' => t('Personal data access')],
        );
        $legalMenu->addChild(static::USER_CONSENT_POLICY, ['route' => 'admin_userconsentpolicy_setting', 'label' => t('User consent policy')]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_MARKETING, $menu);

        return $menu;
    }

    protected function createFilesMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            static::ROOT_FILE,
            ['label' => t('Files')],
        );
        $menu->setExtra('icon', 'file-all');

        $filesMenu = $menu->addChild(static::LIST_FILE, ['route' => 'admin_uploadedfile_list', 'label' => t('Files overview')]);

        $filesMenu->addChild(
            static::EDIT_FILE,
            ['route' => 'admin_uploadedfile_edit', 'label' => t('Editing file'), 'display' => false],
        );
        $filesMenu->addChild(
            static::NEW_FILE,
            ['route' => 'admin_uploadedfile_new', 'label' => t('Upload files'), 'display' => false],
        );

        return $menu;
    }

    protected function createAdministratorsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(
            static::ROOT_ADMINISTRATOR,
            ['label' => t('Administrators')],
        );
        $menu->setExtra('icon', 'person-door-man');

        $administratorViewMenu = $menu->addChild(static::LIST_ADMINISTRATOR, ['route' => 'admin_administrator_list', 'label' => t('Administrators overview')]);

        $administratorViewMenu->addChild(
            static::NEW_ADMINISTRATOR,
            ['route' => 'admin_administrator_new', 'label' => t('New administrator'), 'display' => false],
        );
        $administratorViewMenu->addChild(
            static::EDIT_ADMINISTRATOR,
            ['route' => 'admin_administrator_edit', 'label' => t('Editing administrator'), 'display' => false],
        );
        $administratorViewMenu->addChild(
            static::ENABLE_TWO_FACTOR_AUTHENTICATION,
            ['route' => 'admin_administrator_enable-two-factor-authentication', 'label' => t('Enable two-factor authentication'), 'display' => false],
        );
        $administratorViewMenu->addChild(
            static::DISABLE_TWO_FACTOR_AUTHENTICATION,
            ['route' => 'admin_administrator_disable-two-factor-authentication', 'label' => t('Disable two factor authentication'), 'display' => false],
        );

        $administratorRoleGroupMenu = $menu->addChild(static::LIST_ADMINISTRATOR_ROLE_GROUP, ['route' => 'admin_administratorrolegroup_list', 'label' => t('Role Groups')]);

        $administratorRoleGroupMenu->addChild(
            static::NEW_ADMINISTRATOR_ROLE_GROUP,
            ['route' => 'admin_administratorrolegroup_new', 'label' => t('New administrator role group'), 'display' => false],
        );
        $administratorRoleGroupMenu->addChild(
            static::EDIT_ADMINISTRATOR_ROLE_GROUP,
            ['route' => 'admin_administratorrolegroup_edit', 'label' => t('Editing administrator role group'), 'display' => false],
        );
        $administratorRoleGroupMenu->addChild(
            static::COPY_ADMINISTRATOR_ROLE_GROUP,
            ['route' => 'admin_administratorrolegroup_copy', 'label' => t('Copy administrator role group'), 'display' => false],
        );

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_ADMINISTRATORS, $menu);

        return $menu;
    }

    protected function createSettingsMenu(): ItemInterface
    {
        $menu = $this->menuFactory->createItem(static::ROOT_SETTING, ['label' => t('Settings')]);
        $menu->setExtra('icon', 'gear');

        if ($this->domain->isMultidomain()) {
            $identificationMenu = $menu->addChild(static::SECTION_IDENTIFICATION, ['label' => t('E-shop identification')]);
            $domainsMenu = $identificationMenu->addChild(
                static::LIST_DOMAIN,
                ['route' => 'admin_domain_list', 'label' => t('E-shop identification')],
            );
            $domainsMenu->addChild(
                static::EDIT_DOMAIN,
                ['route' => 'admin_domain_edit', 'label' => t('Editing domain'), 'display' => false],
            );
        }

        $communicationMenu = $menu->addChild(static::SECTION_COMMUNICATION, ['label' => t('Communication with customer')]);
        $communicationMenu->addChild(
            static::MAIL_SETTING,
            ['route' => 'admin_mail_setting', 'label' => t('Email settings')],
        );
        $mailTemplates = $communicationMenu->addChild(
            static::LIST_MAIL_TEMPLATE,
            ['route' => 'admin_mail_template', 'label' => t('Email templates')],
        );
        $mailTemplates->addChild(
            static::EDIT_MAIL_TEMPLATE,
            ['route' => 'admin_mail_edit', 'label' => t('Editing email template'), 'display' => false],
        );

        $listsMenu = $menu->addChild(static::SECTION_LISTS, ['label' => t('Lists and nomenclatures')]);
        $transportsAndPaymentsMenu = $listsMenu->addChild(
            static::LIST_TRANSPORT_AND_PAYMENT,
            ['route' => 'admin_transportandpayment_list', 'label' => t('Shippings and payments')],
        );
        $transportsAndPaymentsMenu->addChild(
            static::NEW_TRANSPORT,
            ['route' => 'admin_transport_new', 'label' => t('New shipping'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            static::EDIT_TRANSPORT,
            ['route' => 'admin_transport_edit', 'label' => t('Editing shipping'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            static::NEW_PAYMENT,
            ['route' => 'admin_payment_new', 'label' => t('New payment'), 'display' => false],
        );
        $transportsAndPaymentsMenu->addChild(
            static::EDIT_PAYMENT,
            ['route' => 'admin_payment_edit', 'label' => t('Editing payment'), 'display' => false],
        );

        $flagsMenu = $listsMenu->addChild(static::LIST_FLAG, ['route' => 'admin_flag_list', 'label' => t('Flags')]);
        $flagsMenu->addChild(static::NEW_FLAG, ['route' => 'admin_flag_new', 'label' => t('New flag'), 'display' => false]);
        $flagsMenu->addChild(static::EDIT_FLAG, ['route' => 'admin_flag_edit', 'label' => t('Editing flag'), 'display' => false]);

        $parametersMenu = $listsMenu->addChild(static::LIST_PARAMETER, ['route' => 'admin_parameter_list', 'label' => t('Parameters')]);
        $parametersMenu->addChild(static::NEW_PARAMETER, ['route' => 'admin_parameter_new', 'display' => false, 'label' => t('New parameter')]);
        $parametersMenu->addChild(static::EDIT_PARAMETER, ['route' => 'admin_parameter_edit', 'display' => false, 'label' => t('Editing parameter')]);
        $parametersMenu->addChild(static::EDIT_PARAMETERS_VALUE, ['route' => 'admin_parametervalues_edit', 'display' => false, 'label' => t('Parameter values')]);

        $parameterGroupsMenu = $listsMenu->addChild(static::LIST_PARAMETER_GROUP, ['route' => 'admin_parametergroup_list', 'label' => t('Parameter groups')]);
        $parameterGroupsMenu->addChild(static::NEW_PARAMETER_GROUP, ['route' => 'admin_parametergroup_new', 'display' => false, 'label' => t('New parameter group')]);
        $parameterGroupsMenu->addChild(static::EDIT_PARAMETER_GROUP, ['route' => 'admin_parametergroup_edit', 'display' => false, 'label' => t('Editing parameter group')]);

        $listsMenu->addChild(
            static::LIST_ORDER_STATUS,
            ['route' => 'admin_orderstatus_list', 'label' => t('Status of orders')],
        );
        $listsMenu->addChild(
            static::LIST_COMPLAINT_STATUS,
            ['route' => 'admin_complaintstatus_list', 'label' => t('Status of complaints')],
        );
        $brandsMenu = $listsMenu->addChild(static::LIST_BRAND, ['route' => 'admin_brand_list', 'label' => t('Brands')]);
        $brandsMenu->addChild(static::NEW_BRAND, ['route' => 'admin_brand_new', 'label' => t('New brand'), 'display' => false]);
        $brandsMenu->addChild(
            static::EDIT_BRAND,
            ['route' => 'admin_brand_edit', 'label' => t('Editing brand'), 'display' => false],
        );
        $listsMenu->addChild(static::LIST_UNIT, ['route' => 'admin_unit_list', 'label' => t('Measurement units')]);
        $countriesMenu = $listsMenu->addChild(
            static::LIST_COUNTRY,
            ['route' => 'admin_country_list', 'label' => t('Countries')],
        );
        $countriesMenu->addChild(
            static::NEW_COUNTRY,
            ['route' => 'admin_country_new', 'label' => t('New country'), 'display' => false],
        );
        $countriesMenu->addChild(
            static::EDIT_COUNTRY,
            ['route' => 'admin_country_edit', 'label' => t('Editing country'), 'display' => false],
        );

        $parameterValueMenu = $listsMenu->addChild(static::LIST_PARAMETER_VALUE, ['route' => 'admin_parametervalue_list', 'label' => t('Parameter value of type color')]);
        $parameterValueMenu->addChild(static::EDIT_PARAMETER_VALUE, ['route' => 'admin_parametervalue_edit', 'display' => false, 'label' => t('Editing parameter value of type color')]);

        $storeMenu = $listsMenu->addChild(static::LIST_STORE, ['route' => 'admin_store_list', 'label' => t('Stores')]);
        $storeMenu->addChild(static::NEW_STORE, ['route' => 'admin_store_new', 'display' => false, 'label' => t('New store')]);
        $storeMenu->addChild(static::EDIT_STORE, ['route' => 'admin_store_edit', 'display' => false, 'label' => t('Edit store')]);

        $closedDayMenu = $listsMenu->addChild(static::LIST_CLOSED_DAY, ['route' => 'admin_closedday_list', 'label' => t('Holidays and internal days')]);
        $closedDayMenu->addChild(static::NEW_CLOSED_DAY, ['route' => 'admin_closedday_new', 'label' => t('New closed day'), 'display' => false]);
        $closedDayMenu->addChild(static::EDIT_CLOSED_DAY, ['route' => 'admin_closedday_edit', 'label' => t('Holiday / internal day detail'), 'display' => false]);
        $closedDayMenu->addChild(static::HOLIDAYS_IMPORT, ['route' => 'admin_closedday_holidaysimport', 'label' => t('Holidays import'), 'display' => false]);

        $seoMenu = $menu->addChild(static::SECTION_SEO, ['label' => t('SEO')]);
        $seoMenu->addChild(static::SEO, ['route' => 'admin_seo_index', 'label' => t('SEO')]);
        $seoMenu->addChild(static::ROBOTS, ['route' => 'admin_seo_robots', 'label' => t('Robots.txt')]);
        $seoMenu->addChild(static::HREFLANG, ['route' => 'admin_seo_hreflang', 'label' => t('Alternate language settings')]);
        $seoMenu->addChild(static::LIST_UNUSED_FRIENDLY_URL, ['route' => 'admin_unused_friendly_url_list', 'label' => t('Unused friendly URL list')]);

        $seoPageMenu = $seoMenu->addChild(static::LIST_SEO_PAGE, ['route' => 'admin_seopage_list', 'label' => t('SEO pages')]);
        $seoPageMenu->addChild(static::NEW_SEO_PAGE, ['route' => 'admin_seopage_new', 'label' => t('New SEO page'), 'display' => false]);
        $seoPageMenu->addChild(static::EDIT_SEO_PAGE, ['route' => 'admin_seopage_edit', 'label' => t('Editing SEO page'), 'display' => false]);

        $categorySeoMenu = $seoMenu->addChild(static::LIST_CATEGORY_SEO, ['route' => 'admin_categoryseo_list', 'label' => t('Extended SEO categories')]);
        $categorySeoMenu->addChild(static::NEW_CATEGORY_SEO, ['route' => 'admin_categoryseo_newcategory', 'label' => t('Extended SEO category - category selection'), 'display' => false]);
        $categorySeoMenu->addChild(static::NEW_CATEGORY_SEO_FILTERS, ['route' => 'admin_categoryseo_newfilters', 'label' => t('Extended SEO category - filters'), 'display' => false]);
        $categorySeoMenu->addChild(static::NEW_CATEGORY_SEO_COMBINATIONS, ['route' => 'admin_categoryseo_newcombinations', 'label' => t('Extended SEO category - combinations'), 'display' => false]);
        $categorySeoMenu->addChild(static::NEW_CATEGORY_SEO_COMBINATION, ['route' => 'admin_categoryseo_readycombination', 'label' => t('Extended SEO category - set combinations with SEO values'), 'display' => false]);

        $contactFormSettingsMenu = $menu->addChild(static::SECTION_CONTACT_FORM, ['label' => t('Contact form')]);
        $contactFormSettingsMenu->addChild(
            static::CONTACT_FORM_SETTINGS,
            ['route' => 'admin_contactformsettings_index', 'label' => t('Contact form')],
        );

        $stockMenu = $menu->addChild(static::SECTION_STOCKS, ['label' => t('Stocking')]);
        $stockMenu->addChild(static::LIST_STOCK, ['route' => 'admin_stock_list', 'label' => t('Warehouses')]);
        $stockMenu->addChild(static::NEW_STOCK, ['route' => 'admin_stock_new', 'display' => false, 'label' => t('New warehouse')]);
        $stockMenu->addChild(static::EDIT_STOCK, ['route' => 'admin_stock_edit', 'display' => false, 'label' => t('Warehouse detail')]);
        $stockMenu->addChild(static::STOCK_SETTINGS, ['route' => 'admin_stock_settings', 'label' => t('Warehouse settings')]);

        $constantsMenu = $menu->addChild(static::SECTION_CONSTANTS, ['label' => t('Language constants')]);
        $constantsListMenu = $constantsMenu->addChild(static::LIST_CONSTANT, ['route' => 'admin_languageconstant_list', 'label' => t('List of language constants')]);
        $constantsListMenu->addChild(static::EDIT_CONSTANT, ['route' => 'admin_languageconstant_edit', 'label' => t('Language constant translation'), 'display' => false]);

        $superadminMenu = $menu->addChild(static::SECTION_SUPERADMIN, ['label' => t('Superadmin')]);
        $superadminMenu->setExtra('superadmin', true);
        $superadminMenu->addChild(static::LIST_MODULE, ['route' => 'admin_superadmin_modules', 'label' => t('Modules')]);
        $superadminMenu->addChild(
            static::PRICING,
            ['route' => 'admin_superadmin_pricing', 'label' => t('Sales including/excluding VAT settings')],
        );
        $superadminMenu->addChild(static::LIST_URL, ['route' => 'admin_superadmin_urls', 'label' => t('URL addresses')]);
        $superadminMenu->addChild(
            static::MAIL_WHITELIST,
            ['route' => 'admin_superadmin_mailwhitelist', 'label' => t('E-mail whitelist settings')],
        );
        $superadminMenu->addChild(static::CLEAR_STOREFRONT_CACHE, ['route' => 'admin_redis_show', 'label' => t('Clean Storefront Cache')]);
        $superadminMenu->addChild(static::CSP_HEADER, ['route' => 'admin_cspheader_setting', 'label' => t('Content-Security-Policy header')]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_SETTINGS, $menu);

        return $menu;
    }

    protected function createIntegrationsMenu(): ItemInterface
    {
        $integrationsMenu = $this->menuFactory->createItem(static::ROOT_INTEGRATIONS, ['label' => t('Integrations')]);
        $integrationsMenu->setExtra('icon', 'puzzle');

        $integrationsMenu->addChild(static::LIST_FEED, ['route' => 'admin_feed_list', 'label' => t('XML Feeds')]);
        $integrationsMenu->addChild(static::MASTRA_DASHBOARD, ['route' => 'admin_mastra_dashboard', 'label' => t('Mastra Assistant')]);
        $integrationsMenu->addChild(static::MASTRA_SQL_DASHBOARD, ['route' => 'admin_mastra_sqldashboard', 'label' => t('SQL Assistant')]);

        $heurekaMenu = $integrationsMenu->addChild(static::SECTION_HEUREKA, ['label' => t('Heureka')]);
        $heurekaMenu->addChild(static::HEUREKA_SETTINGS, ['route' => 'admin_heureka_setting', 'label' => t('Heureka')]);

        $this->dispatchConfigureMenuEvent(ConfigureMenuEvent::SIDE_MENU_INTEGRATIONS, $integrationsMenu);

        return $integrationsMenu;
    }

    protected function dispatchConfigureMenuEvent(string $eventName, ItemInterface $menu): ConfigureMenuEvent
    {
        $event = new ConfigureMenuEvent($this->menuFactory, $menu);

        /** @var \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $configureMenuEvent */
        $configureMenuEvent = $this->eventDispatcher->dispatch($event, $eventName);

        return $configureMenuEvent;
    }
}
