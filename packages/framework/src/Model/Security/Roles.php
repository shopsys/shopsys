<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

class Roles
{
    public const string ROLE_ADMIN = 'ROLE_ADMIN';
    public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const string ROLE_ALL = 'ROLE_ALL';
    public const string ROLE_ALL_VIEW = 'ROLE_ALL_VIEW';

    public const string ROLE_ORDER_FULL = 'ROLE_ORDER_FULL';
    public const string ROLE_ORDER_VIEW = 'ROLE_ORDER_VIEW';

    public const string ROLE_CUSTOMER_FULL = 'ROLE_CUSTOMER_FULL';
    public const string ROLE_CUSTOMER_VIEW = 'ROLE_CUSTOMER_VIEW';

    public const string ROLE_NEWSLETTER_FULL = 'ROLE_NEWSLETTER_FULL';
    public const string ROLE_NEWSLETTER_VIEW = 'ROLE_NEWSLETTER_VIEW';

    public const string ROLE_PROMO_CODE_FULL = 'ROLE_PROMO_CODE_FULL';
    public const string ROLE_PROMO_CODE_VIEW = 'ROLE_PROMO_CODE_VIEW';

    public const string ROLE_SALES_REPRESENTATIVE_FULL = 'ROLE_SALES_REPRESENTATIVE_FULL';
    public const string ROLE_SALES_REPRESENTATIVE_VIEW = 'ROLE_SALES_REPRESENTATIVE_VIEW';

    public const string ROLE_PRODUCT_FULL = 'ROLE_PRODUCT_FULL';
    public const string ROLE_PRODUCT_VIEW = 'ROLE_PRODUCT_VIEW';

    public const string ROLE_TOP_PRODUCT_FULL = 'ROLE_TOP_PRODUCT_FULL';
    public const string ROLE_TOP_PRODUCT_VIEW = 'ROLE_TOP_PRODUCT_VIEW';

    public const string ROLE_BESTSELLING_PRODUCT_FULL = 'ROLE_BESTSELLING_PRODUCT_FULL';
    public const string ROLE_BESTSELLING_PRODUCT_VIEW = 'ROLE_BESTSELLING_PRODUCT_VIEW';

    public const string ROLE_FLAG_FULL = 'ROLE_FLAG_FULL';
    public const string ROLE_FLAG_VIEW = 'ROLE_FLAG_VIEW';

    public const string ROLE_PARAMETER_FULL = 'ROLE_PARAMETER_FULL';
    public const string ROLE_PARAMETER_VIEW = 'ROLE_PARAMETER_VIEW';

    public const string ROLE_UNIT_FULL = 'ROLE_UNIT_FULL';
    public const string ROLE_UNIT_VIEW = 'ROLE_UNIT_VIEW';

    public const string ROLE_CATEGORY_FULL = 'ROLE_CATEGORY_FULL';
    public const string ROLE_CATEGORY_VIEW = 'ROLE_CATEGORY_VIEW';

    public const string ROLE_TOP_CATEGORY_FULL = 'ROLE_TOP_CATEGORY_FULL';
    public const string ROLE_TOP_CATEGORY_VIEW = 'ROLE_TOP_CATEGORY_VIEW';

    public const string ROLE_PRICING_GROUP_FULL = 'ROLE_PRICING_GROUP_FULL';
    public const string ROLE_PRICING_GROUP_VIEW = 'ROLE_PRICING_GROUP_VIEW';

    public const string ROLE_VAT_FULL = 'ROLE_VAT_FULL';
    public const string ROLE_VAT_VIEW = 'ROLE_VAT_VIEW';

    public const string ROLE_ARTICLE_FULL = 'ROLE_ARTICLE_FULL';
    public const string ROLE_ARTICLE_VIEW = 'ROLE_ARTICLE_VIEW';

    public const string ROLE_ADVERT_FULL = 'ROLE_ADVERT_FULL';
    public const string ROLE_ADVERT_VIEW = 'ROLE_ADVERT_VIEW';

    public const string ROLE_SLIDER_ITEM_FULL = 'ROLE_SLIDER_ITEM_FULL';
    public const string ROLE_SLIDER_ITEM_VIEW = 'ROLE_SLIDER_ITEM_VIEW';

    public const string ROLE_NAVIGATION_FULL = 'ROLE_NAVIGATION_FULL';
    public const string ROLE_NAVIGATION_VIEW = 'ROLE_NAVIGATION_VIEW';

    public const string ROLE_BLOG_CATEGORY_FULL = 'ROLE_BLOG_CATEGORY_FULL';
    public const string ROLE_BLOG_CATEGORY_VIEW = 'ROLE_BLOG_CATEGORY_VIEW';

    public const string ROLE_BLOG_ARTICLE_FULL = 'ROLE_BLOG_ARTICLE_FULL';
    public const string ROLE_BLOG_ARTICLE_VIEW = 'ROLE_BLOG_ARTICLE_VIEW';

    public const string ROLE_NOTIFICATION_BAR_FULL = 'ROLE_NOTIFICATION_BAR_FULL';
    public const string ROLE_NOTIFICATION_BAR_VIEW = 'ROLE_NOTIFICATION_BAR_VIEW';

    public const string ROLE_ORDER_SUBMITTED_FULL = 'ROLE_ORDER_SUBMITTED_FULL';
    public const string ROLE_ORDER_SUBMITTED_VIEW = 'ROLE_ORDER_SUBMITTED_VIEW';

    public const string ROLE_LEGAL_CONDITIONS_FULL = 'ROLE_LEGAL_CONDITIONS_FULL';
    public const string ROLE_LEGAL_CONDITIONS_VIEW = 'ROLE_LEGAL_CONDITIONS_VIEW';

    public const string ROLE_PRIVACY_POLICY_FULL = 'ROLE_PRIVACY_POLICY_FULL';
    public const string ROLE_PRIVACY_POLICY_VIEW = 'ROLE_PRIVACY_POLICY_VIEW';

    public const string ROLE_PERSONAL_DATA_FULL = 'ROLE_PERSONAL_DATA_FULL';
    public const string ROLE_PERSONAL_DATA_VIEW = 'ROLE_PERSONAL_DATA_VIEW';

    public const string ROLE_USER_CONSENT_POLICY_FULL = 'ROLE_USER_CONSENT_POLICY_FULL';
    public const string ROLE_USER_CONSENT_POLICY_VIEW = 'ROLE_USER_CONSENT_POLICY_VIEW';

    public const string ROLE_ADMINISTRATOR_FULL = 'ROLE_ADMINISTRATOR_FULL';
    public const string ROLE_ADMINISTRATOR_VIEW = 'ROLE_ADMINISTRATOR_VIEW';

    public const string ROLE_DOMAIN_FULL = 'ROLE_DOMAIN_FULL';
    public const string ROLE_DOMAIN_VIEW = 'ROLE_DOMAIN_VIEW';

    public const string ROLE_SHOP_INFO_FULL = 'ROLE_SHOP_INFO_FULL';
    public const string ROLE_SHOP_INFO_VIEW = 'ROLE_SHOP_INFO_VIEW';

    public const string ROLE_MAIL_SETTING_FULL = 'ROLE_MAIL_SETTING_FULL';
    public const string ROLE_MAIL_SETTING_VIEW = 'ROLE_MAIL_SETTING_VIEW';

    public const string ROLE_MAIL_TEMPLATE_FULL = 'ROLE_MAIL_TEMPLATE_FULL';
    public const string ROLE_MAIL_TEMPLATE_VIEW = 'ROLE_MAIL_TEMPLATE_VIEW';

    public const string ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL = 'ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL';
    public const string ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW = 'ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW';

    public const string ROLE_TRANSPORT_AND_PAYMENT_FULL = 'ROLE_TRANSPORT_AND_PAYMENT_FULL';
    public const string ROLE_TRANSPORT_AND_PAYMENT_VIEW = 'ROLE_TRANSPORT_AND_PAYMENT_VIEW';

    public const string ROLE_ORDER_STATUS_FULL = 'ROLE_ORDER_STATUS_FULL';
    public const string ROLE_ORDER_STATUS_VIEW = 'ROLE_ORDER_STATUS_VIEW';

    public const string ROLE_BRAND_FULL = 'ROLE_BRAND_FULL';
    public const string ROLE_BRAND_VIEW = 'ROLE_BRAND_VIEW';

    public const string ROLE_COUNTRY_FULL = 'ROLE_COUNTRY_FULL';
    public const string ROLE_COUNTRY_VIEW = 'ROLE_COUNTRY_VIEW';

    public const string ROLE_STORE_FULL = 'ROLE_STORE_FULL';
    public const string ROLE_STORE_VIEW = 'ROLE_STORE_VIEW';

    public const string ROLE_PARAMETER_VALUE_FULL = 'ROLE_PARAMETER_VALUE_FULL';
    public const string ROLE_PARAMETER_VALUE_VIEW = 'ROLE_PARAMETER_VALUE_VIEW';

    public const string ROLE_PARAMETER_GROUP_FULL = 'ROLE_PARAMETER_GROUP_FULL';
    public const string ROLE_PARAMETER_GROUP_VIEW = 'ROLE_PARAMETER_GROUP_VIEW';

    public const string ROLE_SEO_FULL = 'ROLE_SEO_FULL';
    public const string ROLE_SEO_VIEW = 'ROLE_SEO_VIEW';

    public const string ROLE_CATEGORY_SEO_FULL = 'ROLE_CATEGORY_SEO_FULL';
    public const string ROLE_CATEGORY_SEO_VIEW = 'ROLE_CATEGORY_SEO_VIEW';

    public const string ROLE_FRIENDLY_URL_FULL = 'ROLE_FRIENDLY_URL_FULL';
    public const string ROLE_FRIENDLY_URL_VIEW = 'ROLE_FRIENDLY_URL_VIEW';

    public const string ROLE_CONTACT_FORM_FULL = 'ROLE_CONTACT_FORM_FULL';
    public const string ROLE_CONTACT_FORM_VIEW = 'ROLE_CONTACT_FORM_VIEW';

    public const string ROLE_STOCK_FULL = 'ROLE_STOCK_FULL';
    public const string ROLE_STOCK_VIEW = 'ROLE_STOCK_VIEW';

    public const string ROLE_FEED_VIEW = 'ROLE_FEED_VIEW';

    public const string ROLE_HEUREKA_FULL = 'ROLE_HEUREKA_FULL';
    public const string ROLE_HEUREKA_VIEW = 'ROLE_HEUREKA_VIEW';

    public const string ROLE_LANGUAGE_CONSTANTS_FULL = 'ROLE_LANGUAGE_CONSTANTS_FULL';
    public const string ROLE_LANGUAGE_CONSTANTS_VIEW = 'ROLE_LANGUAGE_CONSTANTS_VIEW';

    public const string ROLE_TRANSFER_VIEW = 'ROLE_TRANSFER_VIEW';

    public const string ROLE_FILES_FULL = 'ROLE_FILES_FULL';
    public const string ROLE_FILES_VIEW = 'ROLE_FILES_VIEW';

    public const string ROLE_COMPLAINT_FULL = 'ROLE_COMPLAINT_FULL';
    public const string ROLE_COMPLAINT_VIEW = 'ROLE_COMPLAINT_VIEW';

    public const string ROLE_COMPLAINT_STATUS_FULL = 'ROLE_COMPLAINT_STATUS_FULL';
    public const string ROLE_COMPLAINT_STATUS_VIEW = 'ROLE_COMPLAINT_STATUS_VIEW';

    public const string ROLE_INQUIRY_VIEW = 'ROLE_INQUIRY_VIEW';

    public const string ROLE_WATCHDOG_FULL = 'ROLE_WATCHDOG_FULL';
    public const string ROLE_WATCHDOG_VIEW = 'ROLE_WATCHDOG_VIEW';

    public const string ROLE_PRICE_LIST_FULL = 'ROLE_PRICE_LIST_FULL';
    public const string ROLE_PRICE_LIST_VIEW = 'ROLE_PRICE_LIST_VIEW';

    /**
     * @return array<string, string>
     */
    public function getAvailableAdministratorRolesChoices(): array
    {
        return array_flip($this->getAvailableAdministratorRoles());
    }

    /**
     * @return array<array<string, string>>
     */
    public function getAvailableAdministratorRolesGrid(): array
    {
        return [
            [
                static::ROLE_ALL => t('All - full'),
                static::ROLE_ALL_VIEW => t('All - view'),
            ],
            [
                static::ROLE_ORDER_FULL => t('Orders - full'),
                static::ROLE_ORDER_VIEW => t('Orders - view'),
            ],
            [
                static::ROLE_CUSTOMER_FULL => t('Customers - full'),
                static::ROLE_CUSTOMER_VIEW => t('Customers - view'),
            ],
            [
                static::ROLE_NEWSLETTER_FULL => t('Newsletter - full'),
                static::ROLE_NEWSLETTER_VIEW => t('Newsletter - view'),
            ],
            [
                static::ROLE_PROMO_CODE_FULL => t('Promo codes - full'),
                static::ROLE_PROMO_CODE_VIEW => t('Promo codes - view'),
            ],
            [
                static::ROLE_SALES_REPRESENTATIVE_FULL => t('Sales representatives - full'),
                static::ROLE_SALES_REPRESENTATIVE_VIEW => t('Sales representatives - view'),
            ],
            [
                static::ROLE_PRODUCT_FULL => t('Products - full'),
                static::ROLE_PRODUCT_VIEW => t('Products - view'),
            ],
            [
                static::ROLE_TOP_PRODUCT_FULL => t('Top products - full'),
                static::ROLE_TOP_PRODUCT_VIEW => t('Top products - view'),
            ],
            [
                static::ROLE_BESTSELLING_PRODUCT_FULL => t('Bestselling products - full'),
                static::ROLE_BESTSELLING_PRODUCT_VIEW => t('Bestselling products - view'),
            ],
            [
                static::ROLE_FLAG_FULL => t('Flags - full'),
                static::ROLE_FLAG_VIEW => t('Flags - view'),
            ],
            [
                static::ROLE_PARAMETER_FULL => t('Parameters - full'),
                static::ROLE_PARAMETER_VIEW => t('Parameters - view'),
            ],
            [
                static::ROLE_PARAMETER_GROUP_FULL => t('Parameter groups - full'),
                static::ROLE_PARAMETER_GROUP_VIEW => t('Parameter groups - view'),
            ],
            [
                static::ROLE_UNIT_FULL => t('Units - full'),
                static::ROLE_UNIT_VIEW => t('Units - view'),
            ],
            [
                static::ROLE_CATEGORY_FULL => t('Categories - full'),
                static::ROLE_CATEGORY_VIEW => t('Categories - view'),
            ],
            [
                static::ROLE_TOP_CATEGORY_FULL => t('Top categories - full'),
                static::ROLE_TOP_CATEGORY_VIEW => t('Top categories - view'),
            ],
            [
                static::ROLE_PRICING_GROUP_FULL => t('Pricing groups - full'),
                static::ROLE_PRICING_GROUP_VIEW => t('Pricing groups - view'),
            ],
            [
                static::ROLE_VAT_FULL => t('Vats - full'),
                static::ROLE_VAT_VIEW => t('Vats - view'),
            ],
            [
                static::ROLE_ARTICLE_FULL => t('Articles - full'),
                static::ROLE_ARTICLE_VIEW => t('Articles - view'),
            ],
            [
                static::ROLE_ADVERT_FULL => t('Adverts - full'),
                static::ROLE_ADVERT_VIEW => t('Adverts - view'),
            ],
            [
                static::ROLE_SLIDER_ITEM_FULL => t('Slider items - full'),
                static::ROLE_SLIDER_ITEM_VIEW => t('Slider items - view'),
            ],
            [
                static::ROLE_NAVIGATION_FULL => t('Navigation - full'),
                static::ROLE_NAVIGATION_VIEW => t('Navigation - view'),
            ],
            [
                static::ROLE_BLOG_CATEGORY_FULL => t('Blog category - full'),
                static::ROLE_BLOG_CATEGORY_VIEW => t('Blog category - view'),
            ],
            [
                static::ROLE_BLOG_ARTICLE_FULL => t('Blog article - full'),
                static::ROLE_BLOG_ARTICLE_VIEW => t('Blog article - view'),
            ],
            [
                static::ROLE_NOTIFICATION_BAR_FULL => t('Notification bar - full'),
                static::ROLE_NOTIFICATION_BAR_VIEW => t('Notification bar - view'),
            ],
            [
                static::ROLE_ORDER_SUBMITTED_FULL => t('Order submitted page setting - full'),
                static::ROLE_ORDER_SUBMITTED_VIEW => t('Order submitted page setting - view'),
            ],
            [
                static::ROLE_LEGAL_CONDITIONS_FULL => t('Legal conditions article setting - full'),
                static::ROLE_LEGAL_CONDITIONS_VIEW => t('Legal conditions article setting - view'),
            ],
            [
                static::ROLE_PRIVACY_POLICY_FULL => t('Privacy policy article setting - full'),
                static::ROLE_PRIVACY_POLICY_VIEW => t('Privacy policy article setting - view'),
            ],
            [
                static::ROLE_PERSONAL_DATA_FULL => t('Personal data access pages setting - full'),
                static::ROLE_PERSONAL_DATA_VIEW => t('Personal data access pages setting - view'),
            ],
            [
                static::ROLE_USER_CONSENT_POLICY_FULL => t('User consent policy article setting - full'),
                static::ROLE_USER_CONSENT_POLICY_VIEW => t('User consent policy article setting - view'),
            ],
            [
                static::ROLE_ADMINISTRATOR_FULL => t('Administrators - full'),
                static::ROLE_ADMINISTRATOR_VIEW => t('Administrators - view'),
            ],
            [
                static::ROLE_DOMAIN_FULL => t('E-shop identification - full'),
                static::ROLE_DOMAIN_VIEW => t('E-shop identification - view'),
            ],
            [
                static::ROLE_SHOP_INFO_FULL => t('Operator information - full'),
                static::ROLE_SHOP_INFO_VIEW => t('Operator information - view'),
            ],
            [
                static::ROLE_MAIL_SETTING_FULL => t('Mail setting - full'),
                static::ROLE_MAIL_SETTING_VIEW => t('Mail setting - view'),
            ],
            [
                static::ROLE_MAIL_TEMPLATE_FULL => t('Mail templates - full'),
                static::ROLE_MAIL_TEMPLATE_VIEW => t('Mail templates - view'),
            ],
            [
                static::ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL => t('Free transport and payment - full'),
                static::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW => t('Free transport and payment - view'),
            ],
            [
                static::ROLE_TRANSPORT_AND_PAYMENT_FULL => t('Transports and payments - full'),
                static::ROLE_TRANSPORT_AND_PAYMENT_VIEW => t('Transports and payments - view'),
            ],
            [
                static::ROLE_ORDER_STATUS_FULL => t('Order statuses - full'),
                static::ROLE_ORDER_STATUS_VIEW => t('Order statuses - view'),
            ],
            [
                static::ROLE_BRAND_FULL => t('Brands - full'),
                static::ROLE_BRAND_VIEW => t('Brands - view'),
            ],
            [
                static::ROLE_COUNTRY_FULL => t('Countries - full'),
                static::ROLE_COUNTRY_VIEW => t('Countries - view'),
            ],
            [
                static::ROLE_STORE_FULL => t('Stores - full'),
                static::ROLE_STORE_VIEW => t('Stores - view'),
            ],
            [
                static::ROLE_PARAMETER_VALUE_FULL => t('Color parameter values - full'),
                static::ROLE_PARAMETER_VALUE_VIEW => t('Color parameter values - view'),
            ],
            [
                static::ROLE_SEO_FULL => t('SEO - full'),
                static::ROLE_SEO_VIEW => t('SEO - view'),
            ],
            [
                static::ROLE_CATEGORY_SEO_FULL => t('Categories extended SEO - full'),
                static::ROLE_CATEGORY_SEO_VIEW => t('Categories extended SEO - view'),
            ],
            [
                static::ROLE_FRIENDLY_URL_FULL => t('Unused friendly URLs - full'),
                static::ROLE_FRIENDLY_URL_VIEW => t('Unused friendly URLs - view'),
            ],
            [
                static::ROLE_CONTACT_FORM_FULL => t('Contact form - full'),
                static::ROLE_CONTACT_FORM_VIEW => t('Contact form - view'),
            ],
            [
                static::ROLE_STOCK_FULL => t('Warehouses - full'),
                static::ROLE_STOCK_VIEW => t('Warehouses - view'),
            ],
            [
                static::ROLE_HEUREKA_FULL => t('Heureka setting - full'),
                static::ROLE_HEUREKA_VIEW => t('Heureka setting - view'),
            ],
            [
                static::ROLE_LANGUAGE_CONSTANTS_FULL => t('Language constants - full'),
                static::ROLE_LANGUAGE_CONSTANTS_VIEW => t('Language constants - view'),
            ],
            [
                static::ROLE_FEED_VIEW => t('Feeds - view'),
            ],
            [
                static::ROLE_TRANSFER_VIEW => t('Transfers - view'),
            ],
            [
                static::ROLE_FILES_FULL => t('Files - full'),
                static::ROLE_FILES_VIEW => t('Files - view'),
            ],
            [
                static::ROLE_COMPLAINT_FULL => t('Complaints - full'),
                static::ROLE_COMPLAINT_VIEW => t('Complaints - view'),
            ],
            [
                static::ROLE_COMPLAINT_STATUS_FULL => t('Complaint statuses - full'),
                static::ROLE_COMPLAINT_STATUS_VIEW => t('Complaint statuses - view'),
            ],
            [
                static::ROLE_INQUIRY_VIEW => t('Inquiries - view'),
            ],
            [
                static::ROLE_WATCHDOG_FULL => t('Watchdogs - full'),
                static::ROLE_WATCHDOG_VIEW => t('Watchdogs - view'),
            ],
            [
                static::ROLE_PRICE_LIST_FULL => t('Price lists - full'),
                static::ROLE_PRICE_LIST_VIEW => t('Price lists - view'),
            ],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public static function getRolesHierarchy(): array
    {
        return [
            static::ROLE_SUPER_ADMIN => [static::ROLE_ADMIN, static::ROLE_ALL],
            static::ROLE_ALL => [
                static::ROLE_ORDER_FULL,
                static::ROLE_CUSTOMER_FULL,
                static::ROLE_NEWSLETTER_FULL,
                static::ROLE_PROMO_CODE_FULL,
                static::ROLE_SALES_REPRESENTATIVE_FULL,
                static::ROLE_PRODUCT_FULL,
                static::ROLE_TOP_PRODUCT_FULL,
                static::ROLE_BESTSELLING_PRODUCT_FULL,
                static::ROLE_FLAG_FULL,
                static::ROLE_PARAMETER_FULL,
                static::ROLE_UNIT_FULL,
                static::ROLE_CATEGORY_FULL,
                static::ROLE_TOP_CATEGORY_FULL,
                static::ROLE_PRICING_GROUP_FULL,
                static::ROLE_VAT_FULL,
                static::ROLE_ARTICLE_FULL,
                static::ROLE_ADVERT_FULL,
                static::ROLE_SLIDER_ITEM_FULL,
                static::ROLE_NAVIGATION_FULL,
                static::ROLE_BLOG_CATEGORY_FULL,
                static::ROLE_BLOG_ARTICLE_FULL,
                static::ROLE_NOTIFICATION_BAR_FULL,
                static::ROLE_ORDER_SUBMITTED_FULL,
                static::ROLE_LEGAL_CONDITIONS_FULL,
                static::ROLE_PRIVACY_POLICY_FULL,
                static::ROLE_PERSONAL_DATA_FULL,
                static::ROLE_USER_CONSENT_POLICY_FULL,
                static::ROLE_ADMINISTRATOR_FULL,
                static::ROLE_DOMAIN_FULL,
                static::ROLE_SHOP_INFO_FULL,
                static::ROLE_MAIL_SETTING_FULL,
                static::ROLE_MAIL_TEMPLATE_FULL,
                static::ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL,
                static::ROLE_TRANSPORT_AND_PAYMENT_FULL,
                static::ROLE_ORDER_STATUS_FULL,
                static::ROLE_BRAND_FULL,
                static::ROLE_COUNTRY_FULL,
                static::ROLE_STORE_FULL,
                static::ROLE_PARAMETER_VALUE_FULL,
                static::ROLE_PARAMETER_GROUP_FULL,
                static::ROLE_SEO_FULL,
                static::ROLE_CATEGORY_SEO_FULL,
                static::ROLE_FRIENDLY_URL_FULL,
                static::ROLE_CONTACT_FORM_FULL,
                static::ROLE_STOCK_FULL,
                static::ROLE_FEED_VIEW,
                static::ROLE_HEUREKA_FULL,
                static::ROLE_LANGUAGE_CONSTANTS_FULL,
                static::ROLE_TRANSFER_VIEW,
                static::ROLE_FILES_FULL,
                static::ROLE_COMPLAINT_FULL,
                static::ROLE_COMPLAINT_STATUS_FULL,
                static::ROLE_INQUIRY_VIEW,
                static::ROLE_WATCHDOG_FULL,
                static::ROLE_PRICE_LIST_FULL,
            ],
            static::ROLE_ALL_VIEW => [
                static::ROLE_ORDER_VIEW,
                static::ROLE_CUSTOMER_VIEW,
                static::ROLE_NEWSLETTER_VIEW,
                static::ROLE_PROMO_CODE_VIEW,
                static::ROLE_SALES_REPRESENTATIVE_VIEW,
                static::ROLE_PRODUCT_VIEW,
                static::ROLE_TOP_PRODUCT_VIEW,
                static::ROLE_BESTSELLING_PRODUCT_VIEW,
                static::ROLE_FLAG_VIEW,
                static::ROLE_PARAMETER_VIEW,
                static::ROLE_UNIT_VIEW,
                static::ROLE_CATEGORY_VIEW,
                static::ROLE_TOP_CATEGORY_VIEW,
                static::ROLE_PRICING_GROUP_VIEW,
                static::ROLE_VAT_VIEW,
                static::ROLE_ARTICLE_VIEW,
                static::ROLE_ADVERT_VIEW,
                static::ROLE_SLIDER_ITEM_VIEW,
                static::ROLE_NAVIGATION_VIEW,
                static::ROLE_BLOG_CATEGORY_VIEW,
                static::ROLE_BLOG_ARTICLE_VIEW,
                static::ROLE_NOTIFICATION_BAR_VIEW,
                static::ROLE_ORDER_SUBMITTED_VIEW,
                static::ROLE_LEGAL_CONDITIONS_VIEW,
                static::ROLE_PRIVACY_POLICY_VIEW,
                static::ROLE_PERSONAL_DATA_VIEW,
                static::ROLE_USER_CONSENT_POLICY_VIEW,
                static::ROLE_ADMINISTRATOR_VIEW,
                static::ROLE_DOMAIN_VIEW,
                static::ROLE_SHOP_INFO_VIEW,
                static::ROLE_MAIL_SETTING_VIEW,
                static::ROLE_MAIL_TEMPLATE_VIEW,
                static::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW,
                static::ROLE_TRANSPORT_AND_PAYMENT_VIEW,
                static::ROLE_ORDER_STATUS_VIEW,
                static::ROLE_BRAND_VIEW,
                static::ROLE_COUNTRY_VIEW,
                static::ROLE_STORE_VIEW,
                static::ROLE_PARAMETER_VALUE_VIEW,
                static::ROLE_PARAMETER_GROUP_VIEW,
                static::ROLE_SEO_VIEW,
                static::ROLE_CATEGORY_SEO_VIEW,
                static::ROLE_FRIENDLY_URL_VIEW,
                static::ROLE_CONTACT_FORM_VIEW,
                static::ROLE_STOCK_VIEW,
                static::ROLE_FEED_VIEW,
                static::ROLE_HEUREKA_VIEW,
                static::ROLE_LANGUAGE_CONSTANTS_VIEW,
                static::ROLE_TRANSFER_VIEW,
                static::ROLE_FILES_VIEW,
                static::ROLE_COMPLAINT_VIEW,
                static::ROLE_COMPLAINT_STATUS_VIEW,
                static::ROLE_INQUIRY_VIEW,
                static::ROLE_WATCHDOG_VIEW,
                static::ROLE_PRICE_LIST_FULL,
            ],
            static::ROLE_ORDER_FULL => [static::ROLE_ORDER_VIEW],
            static::ROLE_CUSTOMER_FULL => [static::ROLE_CUSTOMER_VIEW],
            static::ROLE_NEWSLETTER_FULL => [static::ROLE_NEWSLETTER_VIEW],
            static::ROLE_PROMO_CODE_FULL => [static::ROLE_PROMO_CODE_VIEW],
            static::ROLE_SALES_REPRESENTATIVE_FULL => [static::ROLE_SALES_REPRESENTATIVE_VIEW],
            static::ROLE_PRODUCT_FULL => [static::ROLE_PRODUCT_VIEW],
            static::ROLE_TOP_PRODUCT_FULL => [static::ROLE_TOP_PRODUCT_VIEW],
            static::ROLE_BESTSELLING_PRODUCT_FULL => [static::ROLE_BESTSELLING_PRODUCT_VIEW],
            static::ROLE_FLAG_FULL => [static::ROLE_FLAG_VIEW],
            static::ROLE_PARAMETER_FULL => [static::ROLE_PARAMETER_VIEW],
            static::ROLE_UNIT_FULL => [static::ROLE_UNIT_VIEW],
            static::ROLE_CATEGORY_FULL => [static::ROLE_CATEGORY_VIEW],
            static::ROLE_TOP_CATEGORY_FULL => [static::ROLE_TOP_CATEGORY_VIEW],
            static::ROLE_PRICING_GROUP_FULL => [static::ROLE_PRICING_GROUP_VIEW],
            static::ROLE_VAT_FULL => [static::ROLE_VAT_VIEW],
            static::ROLE_ARTICLE_FULL => [static::ROLE_ARTICLE_VIEW],
            static::ROLE_ADVERT_FULL => [static::ROLE_ADVERT_VIEW],
            static::ROLE_SLIDER_ITEM_FULL => [static::ROLE_SLIDER_ITEM_VIEW],
            static::ROLE_NAVIGATION_FULL => [static::ROLE_NAVIGATION_VIEW],
            static::ROLE_BLOG_CATEGORY_FULL => [static::ROLE_BLOG_CATEGORY_VIEW],
            static::ROLE_BLOG_ARTICLE_FULL => [static::ROLE_BLOG_ARTICLE_VIEW],
            static::ROLE_NOTIFICATION_BAR_FULL => [static::ROLE_NOTIFICATION_BAR_VIEW],
            static::ROLE_ORDER_SUBMITTED_FULL => [static::ROLE_ORDER_SUBMITTED_VIEW],
            static::ROLE_LEGAL_CONDITIONS_FULL => [static::ROLE_LEGAL_CONDITIONS_VIEW],
            static::ROLE_PRIVACY_POLICY_FULL => [static::ROLE_PRIVACY_POLICY_VIEW],
            static::ROLE_PERSONAL_DATA_FULL => [static::ROLE_PERSONAL_DATA_VIEW],
            static::ROLE_USER_CONSENT_POLICY_FULL => [static::ROLE_USER_CONSENT_POLICY_VIEW],
            static::ROLE_ADMINISTRATOR_FULL => [static::ROLE_ADMINISTRATOR_VIEW],
            static::ROLE_DOMAIN_FULL => [static::ROLE_DOMAIN_VIEW],
            static::ROLE_SHOP_INFO_FULL => [static::ROLE_SHOP_INFO_VIEW],
            static::ROLE_MAIL_SETTING_FULL => [static::ROLE_MAIL_SETTING_VIEW],
            static::ROLE_MAIL_TEMPLATE_FULL => [static::ROLE_MAIL_TEMPLATE_VIEW],
            static::ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL => [static::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW],
            static::ROLE_TRANSPORT_AND_PAYMENT_FULL => [static::ROLE_TRANSPORT_AND_PAYMENT_VIEW],
            static::ROLE_ORDER_STATUS_FULL => [static::ROLE_ORDER_STATUS_VIEW],
            static::ROLE_BRAND_FULL => [static::ROLE_BRAND_VIEW],
            static::ROLE_COUNTRY_FULL => [static::ROLE_COUNTRY_VIEW],
            static::ROLE_STORE_FULL => [static::ROLE_STORE_VIEW],
            static::ROLE_PARAMETER_VALUE_FULL => [static::ROLE_PARAMETER_VALUE_VIEW],
            static::ROLE_PARAMETER_GROUP_FULL => [static::ROLE_PARAMETER_GROUP_VIEW],
            static::ROLE_SEO_FULL => [static::ROLE_SEO_VIEW],
            static::ROLE_CATEGORY_SEO_FULL => [static::ROLE_CATEGORY_SEO_VIEW],
            static::ROLE_FRIENDLY_URL_FULL => [static::ROLE_FRIENDLY_URL_VIEW],
            static::ROLE_CONTACT_FORM_FULL => [static::ROLE_CONTACT_FORM_VIEW],
            static::ROLE_STOCK_FULL => [static::ROLE_STOCK_VIEW],
            static::ROLE_HEUREKA_FULL => [static::ROLE_HEUREKA_VIEW],
            static::ROLE_LANGUAGE_CONSTANTS_FULL => [static::ROLE_LANGUAGE_CONSTANTS_VIEW],
            static::ROLE_FILES_FULL => [static::ROLE_FILES_VIEW],
            static::ROLE_COMPLAINT_FULL => [static::ROLE_COMPLAINT_VIEW],
            static::ROLE_COMPLAINT_STATUS_FULL => [static::ROLE_COMPLAINT_STATUS_VIEW],
            static::ROLE_WATCHDOG_FULL => [static::ROLE_WATCHDOG_VIEW],
            static::ROLE_PRICE_LIST_FULL => [static::ROLE_PRICE_LIST_VIEW],
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Security\AccessControlRule[]
     */
    public static function getAccessControlRules(): array
    {
        return [
            new AccessControlRule('^/%admin_url%/logout', ['PUBLIC_ACCESS']),
            new AccessControlRule('^/%admin_url%/2fa', ['IS_AUTHENTICATED_2FA_IN_PROGRESS']),
            new AccessControlRule('^/%admin_url%/superadmin/', [static::ROLE_SUPER_ADMIN]),
            new AccessControlRule('^/%admin_url%/cron/detail/*', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/cron/*', [static::ROLE_SUPER_ADMIN]),
            new AccessControlRule('^/%admin_url%/currency/', [static::ROLE_SUPER_ADMIN]),
            new AccessControlRule('^/%admin_url%/translation/list/$', [static::ROLE_SUPER_ADMIN]),
            new AccessControlRule('^/%admin_url%/$', ['PUBLIC_ACCESS']),
            new AccessControlRule('^/%admin_url%/authorization/$', ['PUBLIC_ACCESS']),
            new AccessControlRule('^/%admin_url%/order/edit', [static::ROLE_ORDER_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/order/delete', [static::ROLE_ORDER_FULL]),
            new AccessControlRule('^/%admin_url%/order/add-product/', [static::ROLE_ORDER_FULL]),
            new AccessControlRule('^/%admin_url%/order/', [static::ROLE_ORDER_VIEW]),
            new AccessControlRule('^/%admin_url%/inquiry/', [static::ROLE_INQUIRY_VIEW]),
            new AccessControlRule('^/%admin_url%/customer/new', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/edit', [static::ROLE_CUSTOMER_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/customer/delete', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/login-as-user/', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/billing-address/edit', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/edit-personal-data/', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/new-customer-user/', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/admin_customer_send_reset_password/', [static::ROLE_CUSTOMER_VIEW]),
            new AccessControlRule('^/%admin_url%/delivery-address/edit', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/delivery-address/new', [static::ROLE_CUSTOMER_FULL]),
            new AccessControlRule('^/%admin_url%/customer/', [static::ROLE_CUSTOMER_VIEW]),
            new AccessControlRule('^/%admin_url%/watchdog/list', [static::ROLE_WATCHDOG_VIEW]),
            new AccessControlRule('^/%admin_url%/watchdog/detail/*', [static::ROLE_WATCHDOG_VIEW]),
            new AccessControlRule('^/%admin_url%/watchdog/delete/*', [static::ROLE_WATCHDOG_FULL]),
            new AccessControlRule('^/%admin_url%/newsletter/delete', [static::ROLE_NEWSLETTER_FULL]),
            new AccessControlRule('^/%admin_url%/newsletter/export-csv', [static::ROLE_NEWSLETTER_FULL]),
            new AccessControlRule('^/%admin_url%/newsletter/', [static::ROLE_NEWSLETTER_VIEW]),
            new AccessControlRule('^/%admin_url%/promo-code/new', [static::ROLE_PROMO_CODE_FULL]),
            new AccessControlRule('^/%admin_url%/promo-code/edit', [static::ROLE_PROMO_CODE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/promo-code/new-mass-generate', [static::ROLE_PROMO_CODE_FULL]),
            new AccessControlRule('^/%admin_url%/promo-code/delete', [static::ROLE_PROMO_CODE_FULL]),
            new AccessControlRule('^/%admin_url%/promo-code/', [static::ROLE_PROMO_CODE_VIEW]),
            new AccessControlRule('^/%admin_url%/sales-representative/new', [static::ROLE_SALES_REPRESENTATIVE_FULL]),
            new AccessControlRule('^/%admin_url%/sales-representative/edit', [static::ROLE_SALES_REPRESENTATIVE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/sales-representative/delete', [static::ROLE_SALES_REPRESENTATIVE_FULL]),
            new AccessControlRule('^/%admin_url%/sales-representative/', [static::ROLE_SALES_REPRESENTATIVE_VIEW]),
            new AccessControlRule('^/%admin_url%/product/list', [static::ROLE_PRODUCT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/new', [static::ROLE_PRODUCT_FULL]),
            new AccessControlRule('^/%admin_url%/product/edit/catnum-exists', [static::ROLE_PRODUCT_VIEW], ['POST']),
            new AccessControlRule('^/%admin_url%/product/edit', [static::ROLE_PRODUCT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/create-variant', [static::ROLE_PRODUCT_FULL]),
            new AccessControlRule('^/%admin_url%/product/delete', [static::ROLE_PRODUCT_FULL]),
            new AccessControlRule('^/%admin_url%/product/top-product', [static::ROLE_TOP_PRODUCT_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/product/top-product', [static::ROLE_TOP_PRODUCT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/bestselling-product', [static::ROLE_BESTSELLING_PRODUCT_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/product/bestselling-product', [static::ROLE_BESTSELLING_PRODUCT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/flag/delete', [static::ROLE_FLAG_FULL]),
            new AccessControlRule('^/%admin_url%/product/flag/new', [static::ROLE_FLAG_FULL]),
            new AccessControlRule('^/%admin_url%/product/flag/edit', [static::ROLE_FLAG_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/flag/edit', [static::ROLE_FLAG_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/product/flag/', [static::ROLE_FLAG_VIEW]),
            new AccessControlRule('^/%admin_url%/product/parameter/delete', [static::ROLE_PARAMETER_FULL]),
            new AccessControlRule('^/%admin_url%/product/parameter/', [static::ROLE_PARAMETER_VIEW]),
            new AccessControlRule('^/%admin_url%/product/unit/delete', [static::ROLE_UNIT_FULL]),
            new AccessControlRule('^/%admin_url%/product/unit/', [static::ROLE_UNIT_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/product/unit/', [static::ROLE_UNIT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/product/', [static::ROLE_PRODUCT_VIEW]),
            new AccessControlRule('^/%admin_url%/category/new', [static::ROLE_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/category/edit', [static::ROLE_CATEGORY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/category/delete', [static::ROLE_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/category/save-order/', [static::ROLE_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/category/apply-sorting/', [static::ROLE_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/category/top-category', [static::ROLE_TOP_CATEGORY_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/category/top-category', [static::ROLE_TOP_CATEGORY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/category/', [static::ROLE_CATEGORY_VIEW]),
            new AccessControlRule('^/%admin_url%/pricing/group/delete', [static::ROLE_PRICING_GROUP_FULL]),
            new AccessControlRule('^/%admin_url%/pricing/group/list', [static::ROLE_PRICING_GROUP_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/pricing/group/', [static::ROLE_PRICING_GROUP_VIEW]),
            new AccessControlRule('^/%admin_url%/pricing/price-list/new', [static::ROLE_PRICE_LIST_FULL]),
            new AccessControlRule('^/%admin_url%/pricing/price-list/edit', [static::ROLE_PRICE_LIST_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/pricing/price-list/delete', [static::ROLE_PRICE_LIST_FULL]),
            new AccessControlRule('^/%admin_url%/pricing/price-list/', [static::ROLE_PRICE_LIST_VIEW]),
            new AccessControlRule('^/%admin_url%/vat/delete', [static::ROLE_VAT_FULL]),
            new AccessControlRule('^/%admin_url%/vat/list', [static::ROLE_VAT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/vat/', [static::ROLE_VAT_VIEW]),
            new AccessControlRule('^/%admin_url%/article/delete', [static::ROLE_ARTICLE_FULL]),
            new AccessControlRule('^/%admin_url%/article/new', [static::ROLE_ARTICLE_FULL]),
            new AccessControlRule('^/%admin_url%/article/edit', [static::ROLE_ARTICLE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/article/save-ordering/', [static::ROLE_ARTICLE_FULL]),
            new AccessControlRule('^/%admin_url%/article/', [static::ROLE_ARTICLE_VIEW]),
            new AccessControlRule('^/%admin_url%/advert/new', [static::ROLE_ADVERT_FULL]),
            new AccessControlRule('^/%admin_url%/advert/edit', [static::ROLE_ADVERT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/advert/delete', [static::ROLE_ADVERT_FULL]),
            new AccessControlRule('^/%admin_url%/advert/', [static::ROLE_ADVERT_VIEW]),
            new AccessControlRule('^/%admin_url%/slider/item/new', [static::ROLE_SLIDER_ITEM_FULL]),
            new AccessControlRule('^/%admin_url%/slider/item/delete', [static::ROLE_SLIDER_ITEM_FULL]),
            new AccessControlRule('^/%admin_url%/slider/item/edit', [static::ROLE_SLIDER_ITEM_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/slider/', [static::ROLE_SLIDER_ITEM_VIEW]),
            new AccessControlRule('^/%admin_url%/navigation/new', [static::ROLE_NAVIGATION_FULL]),
            new AccessControlRule('^/%admin_url%/navigation/edit', [static::ROLE_NAVIGATION_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/navigation/delete', [static::ROLE_NAVIGATION_FULL]),
            new AccessControlRule('^/%admin_url%/navigation/', [static::ROLE_NAVIGATION_VIEW]),
            new AccessControlRule('^/%admin_url%/blog/category/new', [static::ROLE_BLOG_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/blog/category/edit', [static::ROLE_BLOG_CATEGORY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/blog/category/delete', [static::ROLE_BLOG_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/blog/category/apply-sorting/', [static::ROLE_BLOG_CATEGORY_FULL]),
            new AccessControlRule('^/%admin_url%/blog/category/', [static::ROLE_BLOG_CATEGORY_VIEW]),
            new AccessControlRule('^/%admin_url%/blog/article/new', [static::ROLE_BLOG_ARTICLE_FULL]),
            new AccessControlRule('^/%admin_url%/blog/article/edit', [static::ROLE_BLOG_ARTICLE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/blog/article/delete', [static::ROLE_BLOG_ARTICLE_FULL]),
            new AccessControlRule('^/%admin_url%/blog/article/', [static::ROLE_BLOG_ARTICLE_VIEW]),
            new AccessControlRule('^/%admin_url%/notification-bar/new', [static::ROLE_NOTIFICATION_BAR_FULL]),
            new AccessControlRule('^/%admin_url%/notification-bar/edit', [static::ROLE_NOTIFICATION_BAR_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/notification-bar/delete', [static::ROLE_NOTIFICATION_BAR_FULL]),
            new AccessControlRule('^/%admin_url%/notification-bar/', [static::ROLE_NOTIFICATION_BAR_VIEW]),
            new AccessControlRule('^/%admin_url%/customer-communication/order-submitted', [static::ROLE_ORDER_SUBMITTED_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/customer-communication/order-submitted', [static::ROLE_ORDER_SUBMITTED_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/legal-conditions/setting', [static::ROLE_LEGAL_CONDITIONS_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/legal-conditions/setting', [static::ROLE_LEGAL_CONDITIONS_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/legal-conditions/privacy-policy', [static::ROLE_PRIVACY_POLICY_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/legal-conditions/privacy-policy', [static::ROLE_PRIVACY_POLICY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/personal-data/setting', [static::ROLE_PERSONAL_DATA_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/personal-data/setting', [static::ROLE_PERSONAL_DATA_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/user-consent-policy/setting', [static::ROLE_USER_CONSENT_POLICY_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/user-consent-policy/setting', [static::ROLE_USER_CONSENT_POLICY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/administrator/new', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/delete', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/edit/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/send-reset-password/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/my-account/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/enable-two-factor-authentication/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/disable-two-factor-authentication/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/select-locale/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/administrator/groups/list/', [static::ROLE_ADMINISTRATOR_VIEW]),
            new AccessControlRule('^/%admin_url%/administrator/groups/edit/', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/groups/new/', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/groups/copy/', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/groups/delete/', [static::ROLE_ADMINISTRATOR_FULL]),
            new AccessControlRule('^/%admin_url%/administrator/set-new-password/', ['PUBLIC_ACCESS']),
            new AccessControlRule('^/%admin_url%/administrator/', [static::ROLE_ADMINISTRATOR_VIEW]),
            new AccessControlRule('^/%admin_url%/domain/', [static::ROLE_DOMAIN_VIEW]),
            new AccessControlRule('^/%admin_url%/shop-info/setting', [static::ROLE_SHOP_INFO_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/shop-info/setting', [static::ROLE_SHOP_INFO_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/mail/setting', [static::ROLE_MAIL_SETTING_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/mail/setting', [static::ROLE_MAIL_SETTING_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/mail/create', [static::ROLE_MAIL_TEMPLATE_FULL]),
            new AccessControlRule('^/%admin_url%/mail/edit', [static::ROLE_MAIL_TEMPLATE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/mail/delete', [static::ROLE_MAIL_TEMPLATE_FULL]),
            new AccessControlRule('^/%admin_url%/mail/edit/', [static::ROLE_MAIL_TEMPLATE_VIEW]),
            new AccessControlRule('^/%admin_url%/mail/template/', [static::ROLE_MAIL_TEMPLATE_VIEW]),
            new AccessControlRule('^/%admin_url%/transport-and-payment/free-transport-and-payment-limit/', [static::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/transport-and-payment/free-transport-and-payment-limit/', [static::ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/transport-and-payment/list', [static::ROLE_TRANSPORT_AND_PAYMENT_VIEW]),
            new AccessControlRule('^/%admin_url%/transport/new', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL]),
            new AccessControlRule('^/%admin_url%/transport/edit', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/transport/delete', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL]),
            new AccessControlRule('^/%admin_url%/transport/', [static::ROLE_TRANSPORT_AND_PAYMENT_VIEW]),
            new AccessControlRule('^/%admin_url%/payment/new', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL]),
            new AccessControlRule('^/%admin_url%/payment/edit', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/payment/delete', [static::ROLE_TRANSPORT_AND_PAYMENT_FULL]),
            new AccessControlRule('^/%admin_url%/payment/', [static::ROLE_TRANSPORT_AND_PAYMENT_VIEW]),
            new AccessControlRule('^/%admin_url%/order-status/delete', [static::ROLE_ORDER_STATUS_FULL]),
            new AccessControlRule('^/%admin_url%/order-status/', [static::ROLE_ORDER_STATUS_VIEW]),
            new AccessControlRule('^/%admin_url%/brand/new', [static::ROLE_BRAND_FULL]),
            new AccessControlRule('^/%admin_url%/brand/edit', [static::ROLE_BRAND_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/brand/delete', [static::ROLE_BRAND_FULL]),
            new AccessControlRule('^/%admin_url%/brand/', [static::ROLE_BRAND_VIEW]),
            new AccessControlRule('^/%admin_url%/complaint/edit', [static::ROLE_COMPLAINT_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/complaint/', [static::ROLE_COMPLAINT_VIEW]),
            new AccessControlRule('^/%admin_url%/complaint-status/delete', [static::ROLE_COMPLAINT_STATUS_FULL]),
            new AccessControlRule('^/%admin_url%/complaint-status/', [static::ROLE_COMPLAINT_STATUS_VIEW]),
            new AccessControlRule('^/%admin_url%/country/new', [static::ROLE_COUNTRY_FULL]),
            new AccessControlRule('^/%admin_url%/country/edit', [static::ROLE_COUNTRY_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/country/', [static::ROLE_COUNTRY_VIEW]),
            new AccessControlRule('^/%admin_url%/store/new', [static::ROLE_STORE_FULL]),
            new AccessControlRule('^/%admin_url%/store/edit', [static::ROLE_STORE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/store/delete', [static::ROLE_STORE_FULL]),
            new AccessControlRule('^/%admin_url%/store/setdefault/', [static::ROLE_STORE_FULL]),
            new AccessControlRule('^/%admin_url%/store/', [static::ROLE_STORE_VIEW]),
            new AccessControlRule('^/%admin_url%/parameter-value/', [static::ROLE_PARAMETER_VALUE_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/parameter-value/', [static::ROLE_PARAMETER_VALUE_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/parameter-group/', [static::ROLE_PARAMETER_GROUP_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/parameter-group/', [static::ROLE_PARAMETER_GROUP_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/seo/category/new/ready-combination/category/', [static::ROLE_CATEGORY_SEO_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/seo/category/new/ready-combination/category/', [static::ROLE_CATEGORY_SEO_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/seo/category/new', [static::ROLE_CATEGORY_SEO_FULL]),
            new AccessControlRule('^/%admin_url%/seo/category/ready-combination/delete', [static::ROLE_CATEGORY_SEO_FULL]),
            new AccessControlRule('^/%admin_url%/seo/category', [static::ROLE_CATEGORY_SEO_VIEW]),
            new AccessControlRule('^/%admin_url%/seo/', [static::ROLE_SEO_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/seo/', [static::ROLE_SEO_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/unused-friendly-url/delete', [static::ROLE_FRIENDLY_URL_FULL]),
            new AccessControlRule('^/%admin_url%/unused-friendly-url/', [static::ROLE_FRIENDLY_URL_VIEW]),
            new AccessControlRule('^/%admin_url%/contact-form/', [static::ROLE_CONTACT_FORM_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/contact-form/', [static::ROLE_CONTACT_FORM_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/stock/new', [static::ROLE_STOCK_FULL]),
            new AccessControlRule('^/%admin_url%/stock/edit', [static::ROLE_STOCK_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/stock/delete', [static::ROLE_STOCK_FULL]),
            new AccessControlRule('^/%admin_url%/stock/savesettings/', [static::ROLE_STOCK_FULL]),
            new AccessControlRule('^/%admin_url%/stock/setdefault/', [static::ROLE_STOCK_FULL]),
            new AccessControlRule('^/%admin_url%/stock/', [static::ROLE_STOCK_VIEW]),
            new AccessControlRule('^/%admin_url%/feed/list/', [static::ROLE_FEED_VIEW]),
            new AccessControlRule('^/%admin_url%/heureka/setting/', [static::ROLE_HEUREKA_VIEW], ['GET']),
            new AccessControlRule('^/%admin_url%/heureka/setting/', [static::ROLE_HEUREKA_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/constant/edit', [static::ROLE_LANGUAGE_CONSTANTS_FULL]),
            new AccessControlRule('^/%admin_url%/constant/delete', [static::ROLE_LANGUAGE_CONSTANTS_FULL]),
            new AccessControlRule('^/%admin_url%/constant/list/', [static::ROLE_LANGUAGE_CONSTANTS_VIEW]),
            new AccessControlRule('^/%admin_url%/transfer/issue/list/', [static::ROLE_TRANSFER_VIEW]),
            new AccessControlRule('^/%admin_url%/uploaded-file/new', [static::ROLE_FILES_FULL]),
            new AccessControlRule('^/%admin_url%/uploaded-file/edit', [static::ROLE_FILES_FULL], ['POST']),
            new AccessControlRule('^/%admin_url%/uploaded-file/delete', [static::ROLE_FILES_FULL]),
            new AccessControlRule('^/%admin_url%/file-upload/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/uploaded-file/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/product-picker/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/file-picker/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/_grid/save-form/', [static::ROLE_ALL]),
            new AccessControlRule('^/%admin_url%/_grid/save-ordering/', [static::ROLE_ALL]),
            new AccessControlRule('^/%admin_url%/_grid/get-form/', [static::ROLE_ALL]),
            new AccessControlRule('^/%admin_url%/_grid/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/multidomain/select-domain/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/multidomain/filter-domain/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/domains/filter', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/sso/', [static::ROLE_ADMIN]),
            new AccessControlRule('^/efconnect', [static::ROLE_ADMIN]),
            new AccessControlRule('^/elfinder', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/_error/', ['PUBLIC_ACCESS']),
            new AccessControlRule('^/%admin_url%/dashboard', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/search', [static::ROLE_ADMIN]),
            new AccessControlRule('^/%admin_url%/', [static::ROLE_ALL]),
            new AccessControlRule('^/', ['PUBLIC_ACCESS']),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableAdministratorRoles(): array
    {
        return array_merge(...$this->getAvailableAdministratorRolesGrid());
    }
}
