<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_ALL = 'ROLE_ALL';
    public const ROLE_ALL_VIEW = 'ROLE_ALL_VIEW';

    public const ROLE_ORDER_FULL = 'ROLE_ORDER_FULL';
    public const ROLE_ORDER_VIEW = 'ROLE_ORDER_VIEW';

    public const ROLE_CUSTOMER_FULL = 'ROLE_CUSTOMER_FULL';
    public const ROLE_CUSTOMER_VIEW = 'ROLE_CUSTOMER_VIEW';

    public const ROLE_NEWSLETTER_FULL = 'ROLE_NEWSLETTER_FULL';
    public const ROLE_NEWSLETTER_VIEW = 'ROLE_NEWSLETTER_VIEW';

    public const ROLE_PROMO_CODE_FULL = 'ROLE_PROMO_CODE_FULL';
    public const ROLE_PROMO_CODE_VIEW = 'ROLE_PROMO_CODE_VIEW';

    public const ROLE_PRODUCT_FULL = 'ROLE_PRODUCT_FULL';
    public const ROLE_PRODUCT_VIEW = 'ROLE_PRODUCT_VIEW';

    public const ROLE_TOP_PRODUCT_FULL = 'ROLE_TOP_PRODUCT_FULL';
    public const ROLE_TOP_PRODUCT_VIEW = 'ROLE_TOP_PRODUCT_VIEW';

    public const ROLE_BESTSELLING_PRODUCT_FULL = 'ROLE_BESTSELLING_PRODUCT_FULL';
    public const ROLE_BESTSELLING_PRODUCT_VIEW = 'ROLE_BESTSELLING_PRODUCT_VIEW';

    public const ROLE_FLAG_FULL = 'ROLE_FLAG_FULL';
    public const ROLE_FLAG_VIEW = 'ROLE_FLAG_VIEW';

    public const ROLE_PARAMETER_FULL = 'ROLE_PARAMETER_FULL';
    public const ROLE_PARAMETER_VIEW = 'ROLE_PARAMETER_VIEW';

    public const ROLE_UNIT_FULL = 'ROLE_UNIT_FULL';
    public const ROLE_UNIT_VIEW = 'ROLE_UNIT_VIEW';

    public const ROLE_CATEGORY_FULL = 'ROLE_CATEGORY_FULL';
    public const ROLE_CATEGORY_VIEW = 'ROLE_CATEGORY_VIEW';

    public const ROLE_TOP_CATEGORY_FULL = 'ROLE_TOP_CATEGORY_FULL';
    public const ROLE_TOP_CATEGORY_VIEW = 'ROLE_TOP_CATEGORY_VIEW';

    public const ROLE_PRICING_GROUP_FULL = 'ROLE_PRICING_GROUP_FULL';
    public const ROLE_PRICING_GROUP_VIEW = 'ROLE_PRICING_GROUP_VIEW';

    public const ROLE_VAT_FULL = 'ROLE_VAT_FULL';
    public const ROLE_VAT_VIEW = 'ROLE_VAT_VIEW';

    public const ROLE_ARTICLE_FULL = 'ROLE_ARTICLE_FULL';
    public const ROLE_ARTICLE_VIEW = 'ROLE_ARTICLE_VIEW';

    public const ROLE_ADVERT_FULL = 'ROLE_ADVERT_FULL';
    public const ROLE_ADVERT_VIEW = 'ROLE_ADVERT_VIEW';

    public const ROLE_SLIDER_ITEM_FULL = 'ROLE_SLIDER_ITEM_FULL';
    public const ROLE_SLIDER_ITEM_VIEW = 'ROLE_SLIDER_ITEM_VIEW';

    public const ROLE_NAVIGATION_FULL = 'ROLE_NAVIGATION_FULL';
    public const ROLE_NAVIGATION_VIEW = 'ROLE_NAVIGATION_VIEW';

    public const ROLE_BLOG_CATEGORY_FULL = 'ROLE_BLOG_CATEGORY_FULL';
    public const ROLE_BLOG_CATEGORY_VIEW = 'ROLE_BLOG_CATEGORY_VIEW';

    public const ROLE_BLOG_ARTICLE_FULL = 'ROLE_BLOG_ARTICLE_FULL';
    public const ROLE_BLOG_ARTICLE_VIEW = 'ROLE_BLOG_ARTICLE_VIEW';

    public const ROLE_NOTIFICATION_BAR_FULL = 'ROLE_NOTIFICATION_BAR_FULL';
    public const ROLE_NOTIFICATION_BAR_VIEW = 'ROLE_NOTIFICATION_BAR_VIEW';

    public const ROLE_ORDER_SUBMITTED_FULL = 'ROLE_ORDER_SUBMITTED_FULL';
    public const ROLE_ORDER_SUBMITTED_VIEW = 'ROLE_ORDER_SUBMITTED_VIEW';

    public const ROLE_LEGAL_CONDITIONS_FULL = 'ROLE_LEGAL_CONDITIONS_FULL';
    public const ROLE_LEGAL_CONDITIONS_VIEW = 'ROLE_LEGAL_CONDITIONS_VIEW';

    public const ROLE_PRIVACY_POLICY_FULL = 'ROLE_PRIVACY_POLICY_FULL';
    public const ROLE_PRIVACY_POLICY_VIEW = 'ROLE_PRIVACY_POLICY_VIEW';

    public const ROLE_PERSONAL_DATA_FULL = 'ROLE_PERSONAL_DATA_FULL';
    public const ROLE_PERSONAL_DATA_VIEW = 'ROLE_PERSONAL_DATA_VIEW';

    public const ROLE_USER_CONSENT_POLICY_FULL = 'ROLE_USER_CONSENT_POLICY_FULL';
    public const ROLE_USER_CONSENT_POLICY_VIEW = 'ROLE_USER_CONSENT_POLICY_VIEW';

    public const ROLE_ADMINISTRATOR_FULL = 'ROLE_ADMINISTRATOR_FULL';
    public const ROLE_ADMINISTRATOR_VIEW = 'ROLE_ADMINISTRATOR_VIEW';

    public const ROLE_DOMAIN_FULL = 'ROLE_DOMAIN_FULL';
    public const ROLE_DOMAIN_VIEW = 'ROLE_DOMAIN_VIEW';

    public const ROLE_SHOP_INFO_FULL = 'ROLE_SHOP_INFO_FULL';
    public const ROLE_SHOP_INFO_VIEW = 'ROLE_SHOP_INFO_VIEW';

    public const ROLE_MAIL_SETTING_FULL = 'ROLE_MAIL_SETTING_FULL';
    public const ROLE_MAIL_SETTING_VIEW = 'ROLE_MAIL_SETTING_VIEW';

    public const ROLE_MAIL_TEMPLATE_FULL = 'ROLE_MAIL_TEMPLATE_FULL';
    public const ROLE_MAIL_TEMPLATE_VIEW = 'ROLE_MAIL_TEMPLATE_VIEW';

    public const ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL = 'ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL';
    public const ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW = 'ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW';

    public const ROLE_TRANSPORT_AND_PAYMENT_FULL = 'ROLE_TRANSPORT_AND_PAYMENT_FULL';
    public const ROLE_TRANSPORT_AND_PAYMENT_VIEW = 'ROLE_TRANSPORT_AND_PAYMENT_VIEW';

    public const ROLE_ORDER_STATUS_FULL = 'ROLE_ORDER_STATUS_FULL';
    public const ROLE_ORDER_STATUS_VIEW = 'ROLE_ORDER_STATUS_VIEW';

    public const ROLE_BRAND_FULL = 'ROLE_BRAND_FULL';
    public const ROLE_BRAND_VIEW = 'ROLE_BRAND_VIEW';

    public const ROLE_COUNTRY_FULL = 'ROLE_COUNTRY_FULL';
    public const ROLE_COUNTRY_VIEW = 'ROLE_COUNTRY_VIEW';

    public const ROLE_STORE_FULL = 'ROLE_STORE_FULL';
    public const ROLE_STORE_VIEW = 'ROLE_STORE_VIEW';

    public const ROLE_PARAMETER_VALUE_FULL = 'ROLE_PARAMETER_VALUE_FULL';
    public const ROLE_PARAMETER_VALUE_VIEW = 'ROLE_PARAMETER_VALUE_VIEW';

    public const ROLE_TRANSPORT_TYPE_FULL = 'ROLE_TRANSPORT_TYPE_FULL';
    public const ROLE_TRANSPORT_TYPE_VIEW = 'ROLE_TRANSPORT_TYPE_VIEW';

    public const ROLE_SEO_FULL = 'ROLE_SEO_FULL';
    public const ROLE_SEO_VIEW = 'ROLE_SEO_VIEW';

    public const ROLE_CATEGORY_SEO_FULL = 'ROLE_CATEGORY_SEO_FULL';
    public const ROLE_CATEGORY_SEO_VIEW = 'ROLE_CATEGORY_SEO_VIEW';

    public const ROLE_FRIENDLY_URL_FULL = 'ROLE_FRIENDLY_URL_FULL';
    public const ROLE_FRIENDLY_URL_VIEW = 'ROLE_FRIENDLY_URL_VIEW';

    public const ROLE_CONTACT_FORM_FULL = 'ROLE_CONTACT_FORM_FULL';
    public const ROLE_CONTACT_FORM_VIEW = 'ROLE_CONTACT_FORM_VIEW';

    public const ROLE_STOCK_FULL = 'ROLE_STOCK_FULL';
    public const ROLE_STOCK_VIEW = 'ROLE_STOCK_VIEW';

    public const ROLE_FEED_VIEW = 'ROLE_FEED_VIEW';

    public const ROLE_HEUREKA_FULL = 'ROLE_HEUREKA_FULL';
    public const ROLE_HEUREKA_VIEW = 'ROLE_HEUREKA_VIEW';

    public const ROLE_LANGUAGE_CONSTANTS_FULL = 'ROLE_LANGUAGE_CONSTANTS_FULL';
    public const ROLE_LANGUAGE_CONSTANTS_VIEW = 'ROLE_LANGUAGE_CONSTANTS_VIEW';

    public const ROLE_TRANSFER_VIEW = 'ROLE_TRANSFER_VIEW';

    /**
     * @return array<string, string>
     */
    public static function getAvailableAdministratorRolesChoices(): array
    {
        return array_flip(self::getAvailableAdministratorRoles());
    }

    /**
     * @return array<array<string, string>>
     */
    public static function getAvailableAdministratorRolesGrid(): array
    {
        return [
            [
                self::ROLE_ALL => t('All - full'),
                self::ROLE_ALL_VIEW => t('All - view'),
            ],
            [
                self::ROLE_ORDER_FULL => t('Orders - full'),
                self::ROLE_ORDER_VIEW => t('Orders - view'),
            ],
            [
                self::ROLE_CUSTOMER_FULL => t('Customers - full'),
                self::ROLE_CUSTOMER_VIEW => t('Customers - view'),
            ],
            [
                self::ROLE_NEWSLETTER_FULL => t('Newsletter - full'),
                self::ROLE_NEWSLETTER_VIEW => t('Newsletter - view'),
            ],
            [
                self::ROLE_PROMO_CODE_FULL => t('Promo codes - full'),
                self::ROLE_PROMO_CODE_VIEW => t('Promo codes - view'),
            ],
            [
                self::ROLE_PRODUCT_FULL => t('Products - full'),
                self::ROLE_PRODUCT_VIEW => t('Products - view'),
            ],
            [
                self::ROLE_TOP_PRODUCT_FULL => t('Top products - full'),
                self::ROLE_TOP_PRODUCT_VIEW => t('Top products - view'),
            ],
            [
                self::ROLE_BESTSELLING_PRODUCT_FULL => t('Bestselling products - full'),
                self::ROLE_BESTSELLING_PRODUCT_VIEW => t('Bestselling products - view'),
            ],
            [
                self::ROLE_FLAG_FULL => t('Flags - full'),
                self::ROLE_FLAG_VIEW => t('Flags - view'),
            ],
            [
                self::ROLE_PARAMETER_FULL => t('Parameters - full'),
                self::ROLE_PARAMETER_VIEW => t('Parameters - view'),
            ],
            [
                self::ROLE_UNIT_FULL => t('Units - full'),
                self::ROLE_UNIT_VIEW => t('Units - view'),
            ],
            [
                self::ROLE_CATEGORY_FULL => t('Categories - full'),
                self::ROLE_CATEGORY_VIEW => t('Categories - view'),
            ],
            [
                self::ROLE_TOP_CATEGORY_FULL => t('Top categories - full'),
                self::ROLE_TOP_CATEGORY_VIEW => t('Top categories - view'),
            ],
            [
                self::ROLE_PRICING_GROUP_FULL => t('Pricing groups - full'),
                self::ROLE_PRICING_GROUP_VIEW => t('Pricing groups - view'),
            ],
            [
                self::ROLE_VAT_FULL => t('Vats - full'),
                self::ROLE_VAT_VIEW => t('Vats - view'),
            ],
            [
                self::ROLE_ARTICLE_FULL => t('Articles - full'),
                self::ROLE_ARTICLE_VIEW => t('Articles - view'),
            ],
            [
                self::ROLE_ADVERT_FULL => t('Adverts - full'),
                self::ROLE_ADVERT_VIEW => t('Adverts - view'),
            ],
            [
                self::ROLE_SLIDER_ITEM_FULL => t('Slider items - full'),
                self::ROLE_SLIDER_ITEM_VIEW => t('Slider items - view'),
            ],
            [
                self::ROLE_NAVIGATION_FULL => t('Navigation - full'),
                self::ROLE_NAVIGATION_VIEW => t('Navigation - view'),
            ],
            [
                self::ROLE_BLOG_CATEGORY_FULL => t('Blog category - full'),
                self::ROLE_BLOG_CATEGORY_VIEW => t('Blog category - view'),
            ],
            [
                self::ROLE_BLOG_ARTICLE_FULL => t('Blog article - full'),
                self::ROLE_BLOG_ARTICLE_VIEW => t('Blog article - view'),
            ],
            [
                self::ROLE_NOTIFICATION_BAR_FULL => t('Notification bar - full'),
                self::ROLE_NOTIFICATION_BAR_VIEW => t('Notification bar - view'),
            ],
            [
                self::ROLE_ORDER_SUBMITTED_FULL => t('Order submitted page setting - full'),
                self::ROLE_ORDER_SUBMITTED_VIEW => t('Order submitted page setting - view'),
            ],
            [
                self::ROLE_LEGAL_CONDITIONS_FULL => t('Legal conditions article setting - full'),
                self::ROLE_LEGAL_CONDITIONS_VIEW => t('Legal conditions article setting - view'),
            ],
            [
                self::ROLE_PRIVACY_POLICY_FULL => t('Privacy policy article setting - full'),
                self::ROLE_PRIVACY_POLICY_VIEW => t('Privacy policy article setting - view'),
            ],
            [
                self::ROLE_PERSONAL_DATA_FULL => t('Personal data access pages setting - full'),
                self::ROLE_PERSONAL_DATA_VIEW => t('Personal data access pages setting - view'),
            ],
            [
                self::ROLE_USER_CONSENT_POLICY_FULL => t('User consent policy article setting - full'),
                self::ROLE_USER_CONSENT_POLICY_VIEW => t('User consent policy article setting - view'),
            ],
            [
                self::ROLE_ADMINISTRATOR_FULL => t('Administrators - full'),
                self::ROLE_ADMINISTRATOR_VIEW => t('Administrators - view'),
            ],
            [
                self::ROLE_DOMAIN_FULL => t('E-shop identification - full'),
                self::ROLE_DOMAIN_VIEW => t('E-shop identification - view'),
            ],
            [
                self::ROLE_SHOP_INFO_FULL => t('Operator information - full'),
                self::ROLE_SHOP_INFO_VIEW => t('Operator information - view'),
            ],
            [
                self::ROLE_MAIL_SETTING_FULL => t('Mail setting - full'),
                self::ROLE_MAIL_SETTING_VIEW => t('Mail setting - view'),
            ],
            [
                self::ROLE_MAIL_TEMPLATE_FULL => t('Mail templates - full'),
                self::ROLE_MAIL_TEMPLATE_VIEW => t('Mail templates - view'),
            ],
            [
                self::ROLE_FREE_TRANSPORT_AND_PAYMENT_FULL => t('Free transport and payment - full'),
                self::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW => t('Free transport and payment - view'),
            ],
            [
                self::ROLE_TRANSPORT_AND_PAYMENT_FULL => t('Transports and payments - full'),
                self::ROLE_TRANSPORT_AND_PAYMENT_VIEW => t('Transports and payments - view'),
            ],
            [
                self::ROLE_ORDER_STATUS_FULL => t('Order statuses - full'),
                self::ROLE_ORDER_STATUS_VIEW => t('Order statuses - view'),
            ],
            [
                self::ROLE_BRAND_FULL => t('Brands - full'),
                self::ROLE_BRAND_VIEW => t('Brands - view'),
            ],
            [
                self::ROLE_COUNTRY_FULL => t('Countries - full'),
                self::ROLE_COUNTRY_VIEW => t('Countries - view'),
            ],
            [
                self::ROLE_STORE_FULL => t('Stores - full'),
                self::ROLE_STORE_VIEW => t('Stores - view'),
            ],
            [
                self::ROLE_PARAMETER_VALUE_FULL => t('Color parameter values - full'),
                self::ROLE_PARAMETER_VALUE_VIEW => t('Color parameter values - view'),
            ],
            [
                self::ROLE_TRANSPORT_TYPE_FULL => t('Transport types - full'),
                self::ROLE_TRANSPORT_TYPE_VIEW => t('Transport types - view'),
            ],
            [
                self::ROLE_SEO_FULL => t('SEO - full'),
                self::ROLE_SEO_VIEW => t('SEO - view'),
            ],
            [
                self::ROLE_CATEGORY_SEO_FULL => t('Categories extended SEO - full'),
                self::ROLE_CATEGORY_SEO_VIEW => t('Categories extended SEO - view'),
            ],
            [
                self::ROLE_FRIENDLY_URL_FULL => t('Unused friendly URLs - full'),
                self::ROLE_FRIENDLY_URL_VIEW => t('Unused friendly URLs - view'),
            ],
            [
                self::ROLE_CONTACT_FORM_FULL => t('Contact form - full'),
                self::ROLE_CONTACT_FORM_VIEW => t('Contact form - view'),
            ],
            [
                self::ROLE_STOCK_FULL => t('Warehouses - full'),
                self::ROLE_STOCK_VIEW => t('Warehouses - view'),
            ],
            [
                self::ROLE_HEUREKA_FULL => t('Heureka setting - full'),
                self::ROLE_HEUREKA_VIEW => t('Heureka setting - view'),
            ],
            [
                self::ROLE_LANGUAGE_CONSTANTS_FULL => t('Language constants - full'),
                self::ROLE_LANGUAGE_CONSTANTS_VIEW => t('Language constants - view'),
            ],
            [
                self::ROLE_FEED_VIEW => t('Feeds - view'),
            ],
            [
                self::ROLE_TRANSFER_VIEW => t('Transfers - view'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getAvailableAdministratorRoles(): array
    {
        return array_merge(...self::getAvailableAdministratorRolesGrid());
    }
}
