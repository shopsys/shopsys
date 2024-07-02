<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

class MenuItemsGrantedRolesSetting
{
    public const string MENU_ITEM_PATH_SEPARATOR = ' -> ';

    /**
     * @return array<string, string[]>
     */
    public static function getGrantedRolesByMenuItems(): array
    {
        $pagesGrantedRoles = self::getPagesGrantedRoles();
        $sectionsGrantedRoles = self::getSectionsGrantedRoles($pagesGrantedRoles);

        return array_merge($pagesGrantedRoles, $sectionsGrantedRoles);
    }

    /**
     * @return array<string, string[]>
     */
    protected static function getPagesGrantedRoles(): array
    {
        return [
            'orders' => [
                Roles::ROLE_ORDER_VIEW,
            ],
            'customers' . self::MENU_ITEM_PATH_SEPARATOR . 'customers_overview' => [
                Roles::ROLE_CUSTOMER_VIEW,
            ],
            'customers' . self::MENU_ITEM_PATH_SEPARATOR . 'newsletter' => [
                Roles::ROLE_NEWSLETTER_VIEW,
            ],
            'customers' . self::MENU_ITEM_PATH_SEPARATOR . 'promo_codes' => [
                Roles::ROLE_PROMO_CODE_VIEW,
            ],
            'products' . self::MENU_ITEM_PATH_SEPARATOR . 'products' => [
                Roles::ROLE_PRODUCT_VIEW,
            ],
            'products' . self::MENU_ITEM_PATH_SEPARATOR . 'categories' => [
                Roles::ROLE_CATEGORY_VIEW,
            ],
            'pricing' . self::MENU_ITEM_PATH_SEPARATOR . 'pricing_groups' => [
                Roles::ROLE_PRICING_GROUP_VIEW,
            ],
            'pricing' . self::MENU_ITEM_PATH_SEPARATOR . 'vat' => [
                Roles::ROLE_VAT_VIEW,
            ],
            'pricing' . self::MENU_ITEM_PATH_SEPARATOR . 'free_transport_and_payment' => [
                Roles::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'articles' => [
                Roles::ROLE_ARTICLE_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'adverts' => [
                Roles::ROLE_ADVERT_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'bestselling_products' => [
                Roles::ROLE_BESTSELLING_PRODUCT_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'homepage' . self::MENU_ITEM_PATH_SEPARATOR . 'banners' => [
                Roles::ROLE_SLIDER_ITEM_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'homepage' . self::MENU_ITEM_PATH_SEPARATOR . 'promoted_products' => [
                Roles::ROLE_TOP_PRODUCT_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'homepage' . self::MENU_ITEM_PATH_SEPARATOR . 'promoted_categories' => [
                Roles::ROLE_TOP_CATEGORY_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'navigation' => [
                Roles::ROLE_NAVIGATION_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'blog' . self::MENU_ITEM_PATH_SEPARATOR . 'blogCategories' => [
                Roles::ROLE_BLOG_CATEGORY_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'blog' . self::MENU_ITEM_PATH_SEPARATOR . 'blogArticles' => [
                Roles::ROLE_BLOG_ARTICLE_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'notification_bar' => [
                Roles::ROLE_NOTIFICATION_BAR_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'order_confirmation' => [
                Roles::ROLE_ORDER_SUBMITTED_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'legal' . self::MENU_ITEM_PATH_SEPARATOR . 'terms_and_conditions' => [
                Roles::ROLE_LEGAL_CONDITIONS_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'legal' . self::MENU_ITEM_PATH_SEPARATOR . 'privace_policy' => [
                Roles::ROLE_PRIVACY_POLICY_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'legal' . self::MENU_ITEM_PATH_SEPARATOR . 'personal_data' => [
                Roles::ROLE_PERSONAL_DATA_VIEW,
            ],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'legal' . self::MENU_ITEM_PATH_SEPARATOR . 'user-consent-policy' => [
                Roles::ROLE_USER_CONSENT_POLICY_VIEW,
            ],
            'administrators' => [
                Roles::ROLE_ADMINISTRATOR_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'identification' . self::MENU_ITEM_PATH_SEPARATOR . 'domains' => [
                Roles::ROLE_DOMAIN_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'identification' . self::MENU_ITEM_PATH_SEPARATOR . 'shop_info' => [
                Roles::ROLE_SHOP_INFO_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'communication' . self::MENU_ITEM_PATH_SEPARATOR . 'mail_settings' => [
                Roles::ROLE_MAIL_SETTING_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'communication' . self::MENU_ITEM_PATH_SEPARATOR . 'mail_templates' => [
                Roles::ROLE_MAIL_TEMPLATE_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'transports_and_payments' => [
                Roles::ROLE_TRANSPORT_AND_PAYMENT_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'flags' => [
                Roles::ROLE_FLAG_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'parameters' => [
                Roles::ROLE_PARAMETER_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'order_statuses' => [
                Roles::ROLE_ORDER_STATUS_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'brands' => [
                Roles::ROLE_BRAND_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'units' => [
                Roles::ROLE_UNIT_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'countries' => [
                Roles::ROLE_COUNTRY_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'stores' => [
                Roles::ROLE_STORE_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'parameter_values' => [
                Roles::ROLE_PARAMETER_VALUE_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' . self::MENU_ITEM_PATH_SEPARATOR . 'transport_type' => [
                Roles::ROLE_TRANSPORT_TYPE_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'seo' . self::MENU_ITEM_PATH_SEPARATOR . 'seo' => [
                Roles::ROLE_SEO_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'seo' . self::MENU_ITEM_PATH_SEPARATOR . 'categorySeo' => [
                Roles::ROLE_CATEGORY_SEO_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'seo' . self::MENU_ITEM_PATH_SEPARATOR . 'unusedFriendlyUrlList' => [
                Roles::ROLE_FRIENDLY_URL_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'contact_form_settings' => [
                Roles::ROLE_CONTACT_FORM_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'stocks' => [
                Roles::ROLE_STOCK_VIEW,
            ],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'constants' => [
                Roles::ROLE_LANGUAGE_CONSTANTS_VIEW,
            ],
            'integrations' . self::MENU_ITEM_PATH_SEPARATOR . 'feeds' => [
                Roles::ROLE_FEED_VIEW,
            ],
            'integrations' . self::MENU_ITEM_PATH_SEPARATOR . 'heureka' => [
                Roles::ROLE_HEUREKA_VIEW,
            ],
        ];
    }

    /**
     * @param array<string, string[]> $pagesGrantedRoles
     * @return array<string, string[]>
     */
    protected static function getSectionsGrantedRoles(array $pagesGrantedRoles): array
    {
        $sectionsGrantedRoles = [
            'customers' => [],
            'products' => [],
            'pricing' => [],
            'marketing' => [],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'homepage' => [],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'blog' => [],
            'marketing' . self::MENU_ITEM_PATH_SEPARATOR . 'legal' => [],
            'settings' => [],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'identification' => [],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'communication' => [],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'lists' => [],
            'settings' . self::MENU_ITEM_PATH_SEPARATOR . 'seo' => [],
            'integrations' => [],
        ];

        foreach ($pagesGrantedRoles as $pagePath => $pageGrantedRoles) {
            foreach ($sectionsGrantedRoles as $sectionName => $sectionGrantedRoles) {
                if (strpos($pagePath, $sectionName) === 0) {
                    $sectionsGrantedRoles[$sectionName] = array_merge($sectionGrantedRoles, $pageGrantedRoles);
                }
            }
        }

        return $sectionsGrantedRoles;
    }
}
