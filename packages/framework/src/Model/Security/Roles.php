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
    public const string ROLE_FEED_FULL = 'ROLE_FEED_FULL';

    public const string ROLE_HEUREKA_FULL = 'ROLE_HEUREKA_FULL';
    public const string ROLE_HEUREKA_VIEW = 'ROLE_HEUREKA_VIEW';

    public const string ROLE_LANGUAGE_CONSTANTS_FULL = 'ROLE_LANGUAGE_CONSTANTS_FULL';
    public const string ROLE_LANGUAGE_CONSTANTS_VIEW = 'ROLE_LANGUAGE_CONSTANTS_VIEW';

    public const string ROLE_TRANSFER_VIEW = 'ROLE_TRANSFER_VIEW';
    public const string ROLE_TRANSFER_FULL = 'ROLE_TRANSFER_FULL';

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

    public const string ROLE_CLOSED_DAYS_FULL = 'ROLE_CLOSED_DAYS_FULL';
    public const string ROLE_CLOSED_DAYS_VIEW = 'ROLE_CLOSED_DAYS_VIEW';
}
