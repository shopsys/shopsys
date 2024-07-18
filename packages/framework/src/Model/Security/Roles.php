<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

class Roles
{
    public const string ROLE_ADMIN = 'ROLE_ADMIN';
    public const string ROLE_LOGGED_CUSTOMER = 'ROLE_LOGGED_CUSTOMER';
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

    public const string ROLE_TRANSPORT_TYPE_FULL = 'ROLE_TRANSPORT_TYPE_FULL';
    public const string ROLE_TRANSPORT_TYPE_VIEW = 'ROLE_TRANSPORT_TYPE_VIEW';

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
                static::ROLE_TRANSPORT_TYPE_FULL => t('Transport types - full'),
                static::ROLE_TRANSPORT_TYPE_VIEW => t('Transport types - view'),
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
