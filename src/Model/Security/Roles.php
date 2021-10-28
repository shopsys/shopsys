<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_ALL = 'ROLE_ALL';

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

    /**
     * @return array<string, string>
     */
    public static function getAvailableAdministratorRoles(): array
    {
        return [
            self::ROLE_ALL => t('All'),
            self::ROLE_ORDER_FULL => t('Orders - full'),
            self::ROLE_ORDER_VIEW => t('Orders - view'),
            self::ROLE_CUSTOMER_FULL => t('Customers - full'),
            self::ROLE_CUSTOMER_VIEW => t('Customers - view'),
            self::ROLE_NEWSLETTER_FULL => t('Newsletter - full'),
            self::ROLE_NEWSLETTER_VIEW => t('Newsletter - view'),
            self::ROLE_PROMO_CODE_FULL => t('Promo codes - full'),
            self::ROLE_PROMO_CODE_VIEW => t('Promo codes - view'),
            self::ROLE_PRODUCT_FULL => t('Products - full'),
            self::ROLE_PRODUCT_VIEW => t('Products - view'),
            self::ROLE_TOP_PRODUCT_FULL => t('Top products - full'),
            self::ROLE_TOP_PRODUCT_VIEW => t('Top products - view'),
            self::ROLE_BESTSELLING_PRODUCT_FULL => t('Bestselling products - full'),
            self::ROLE_BESTSELLING_PRODUCT_VIEW => t('Bestselling products - view'),
            self::ROLE_FLAG_FULL => t('Flags - full'),
            self::ROLE_FLAG_VIEW => t('Flags - view'),
            self::ROLE_PARAMETER_FULL => t('Parameters - full'),
            self::ROLE_PARAMETER_VIEW => t('Parameters - view'),
            self::ROLE_UNIT_FULL => t('Units - full'),
            self::ROLE_UNIT_VIEW => t('Units - view'),
            self::ROLE_CATEGORY_FULL => t('Categories - full'),
            self::ROLE_CATEGORY_VIEW => t('Categories - view'),
            self::ROLE_TOP_CATEGORY_FULL => t('Top categories - full'),
            self::ROLE_TOP_CATEGORY_VIEW => t('Top categories - view'),
            self::ROLE_PRICING_GROUP_FULL => t('Pricing groups - full'),
            self::ROLE_PRICING_GROUP_VIEW => t('Pricing groups - view'),
            self::ROLE_VAT_FULL => t('Vats - full'),
            self::ROLE_VAT_VIEW => t('Vats - view'),
            self::ROLE_ARTICLE_FULL => t('Articles - full'),
            self::ROLE_ARTICLE_VIEW => t('Articles - view'),
            self::ROLE_ADVERT_FULL => t('Adverts - full'),
            self::ROLE_ADVERT_VIEW => t('Adverts - view'),
            self::ROLE_SLIDER_ITEM_FULL => t('Slider items - full'),
            self::ROLE_SLIDER_ITEM_VIEW => t('Slider items - view'),
            self::ROLE_NAVIGATION_FULL => t('Navigation - full'),
            self::ROLE_NAVIGATION_VIEW => t('Navigation - view'),
            self::ROLE_BLOG_CATEGORY_FULL => t('Blog category - full'),
            self::ROLE_BLOG_CATEGORY_VIEW => t('Blog category - view'),
            self::ROLE_BLOG_ARTICLE_FULL => t('Blog article - full'),
            self::ROLE_BLOG_ARTICLE_VIEW => t('Blog article - view'),
            self::ROLE_NOTIFICATION_BAR_FULL => t('Notification bar - full'),
            self::ROLE_NOTIFICATION_BAR_VIEW => t('Notification bar - view'),
            self::ROLE_ORDER_SUBMITTED_FULL => t('Order submitted page setting - full'),
            self::ROLE_ORDER_SUBMITTED_VIEW => t('Order submitted page setting - view'),
            self::ROLE_LEGAL_CONDITIONS_FULL => t('Legal conditions article setting - full'),
            self::ROLE_LEGAL_CONDITIONS_VIEW => t('Legal conditions article setting - view'),
            self::ROLE_PRIVACY_POLICY_FULL => t('Privacy policy article setting - full'),
            self::ROLE_PRIVACY_POLICY_VIEW => t('Privacy policy article setting - view'),
            self::ROLE_PERSONAL_DATA_FULL => t('Personal data access pages setting - full'),
            self::ROLE_PERSONAL_DATA_VIEW => t('Personal data access pages setting - view'),
        ];
    }
}
