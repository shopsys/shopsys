<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

class MenuItemsGrantedRolesSetting
{
    public const string MENU_ITEM_PATH_SEPARATOR = ' -> ';

    /**
     * @return array<string, string[]>
     */
    public function getGrantedRolesByMenuItems(): array
    {
        $pagesGrantedRoles = $this->getPagesGrantedRoles();
        $sectionsGrantedRoles = $this->getSectionsGrantedRoles($pagesGrantedRoles);

        return array_merge($pagesGrantedRoles, $sectionsGrantedRoles);
    }

    /**
     * @return array<string, string[]>
     */
    protected function getPagesGrantedRoles(): array
    {
        return [
            'orders' => [
                Roles::ROLE_ORDER_VIEW,
            ],
            'customers' . static::MENU_ITEM_PATH_SEPARATOR . 'customers_overview' => [
                Roles::ROLE_CUSTOMER_VIEW,
            ],
            'customers' . static::MENU_ITEM_PATH_SEPARATOR . 'newsletter' => [
                Roles::ROLE_NEWSLETTER_VIEW,
            ],
            'customers' . static::MENU_ITEM_PATH_SEPARATOR . 'complaint' => [
                Roles::ROLE_COMPLAINT_VIEW,
            ],
            'customers' . static::MENU_ITEM_PATH_SEPARATOR . 'promo_codes' => [
                Roles::ROLE_PROMO_CODE_VIEW,
            ],
            'products' . static::MENU_ITEM_PATH_SEPARATOR . 'products' => [
                Roles::ROLE_PRODUCT_VIEW,
            ],
            'products' . static::MENU_ITEM_PATH_SEPARATOR . 'categories' => [
                Roles::ROLE_CATEGORY_VIEW,
            ],
            'pricing' . static::MENU_ITEM_PATH_SEPARATOR . 'pricing_groups' => [
                Roles::ROLE_PRICING_GROUP_VIEW,
            ],
            'pricing' . static::MENU_ITEM_PATH_SEPARATOR . 'vat' => [
                Roles::ROLE_VAT_VIEW,
            ],
            'pricing' . static::MENU_ITEM_PATH_SEPARATOR . 'free_transport_and_payment' => [
                Roles::ROLE_FREE_TRANSPORT_AND_PAYMENT_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'articles' => [
                Roles::ROLE_ARTICLE_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'adverts' => [
                Roles::ROLE_ADVERT_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'bestselling_products' => [
                Roles::ROLE_BESTSELLING_PRODUCT_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'homepage' . static::MENU_ITEM_PATH_SEPARATOR . 'banners' => [
                Roles::ROLE_SLIDER_ITEM_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'homepage' . static::MENU_ITEM_PATH_SEPARATOR . 'promoted_products' => [
                Roles::ROLE_TOP_PRODUCT_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'homepage' . static::MENU_ITEM_PATH_SEPARATOR . 'promoted_categories' => [
                Roles::ROLE_TOP_CATEGORY_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'navigation' => [
                Roles::ROLE_NAVIGATION_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'blog' . static::MENU_ITEM_PATH_SEPARATOR . 'blogCategories' => [
                Roles::ROLE_BLOG_CATEGORY_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'blog' . static::MENU_ITEM_PATH_SEPARATOR . 'blogArticles' => [
                Roles::ROLE_BLOG_ARTICLE_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'notification_bar' => [
                Roles::ROLE_NOTIFICATION_BAR_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'order_confirmation' => [
                Roles::ROLE_ORDER_SUBMITTED_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'legal' . static::MENU_ITEM_PATH_SEPARATOR . 'terms_and_conditions' => [
                Roles::ROLE_LEGAL_CONDITIONS_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'legal' . static::MENU_ITEM_PATH_SEPARATOR . 'privace_policy' => [
                Roles::ROLE_PRIVACY_POLICY_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'legal' . static::MENU_ITEM_PATH_SEPARATOR . 'personal_data' => [
                Roles::ROLE_PERSONAL_DATA_VIEW,
            ],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'legal' . static::MENU_ITEM_PATH_SEPARATOR . 'user-consent-policy' => [
                Roles::ROLE_USER_CONSENT_POLICY_VIEW,
            ],
            'administrators' => [
                Roles::ROLE_ADMINISTRATOR_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'identification' . static::MENU_ITEM_PATH_SEPARATOR . 'domains' => [
                Roles::ROLE_DOMAIN_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'identification' . static::MENU_ITEM_PATH_SEPARATOR . 'shop_info' => [
                Roles::ROLE_SHOP_INFO_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'communication' . static::MENU_ITEM_PATH_SEPARATOR . 'mail_settings' => [
                Roles::ROLE_MAIL_SETTING_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'communication' . static::MENU_ITEM_PATH_SEPARATOR . 'mail_templates' => [
                Roles::ROLE_MAIL_TEMPLATE_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'transports_and_payments' => [
                Roles::ROLE_TRANSPORT_AND_PAYMENT_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'flags' => [
                Roles::ROLE_FLAG_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'parameters' => [
                Roles::ROLE_PARAMETER_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'order_statuses' => [
                Roles::ROLE_ORDER_STATUS_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'complaint_statuses' => [
                Roles::ROLE_COMPLAINT_STATUS_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'brands' => [
                Roles::ROLE_BRAND_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'units' => [
                Roles::ROLE_UNIT_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'countries' => [
                Roles::ROLE_COUNTRY_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'stores' => [
                Roles::ROLE_STORE_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'parameter_values' => [
                Roles::ROLE_PARAMETER_VALUE_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' . static::MENU_ITEM_PATH_SEPARATOR . 'transport_type' => [
                Roles::ROLE_TRANSPORT_TYPE_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'seo' . static::MENU_ITEM_PATH_SEPARATOR . 'seo' => [
                Roles::ROLE_SEO_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'seo' . static::MENU_ITEM_PATH_SEPARATOR . 'categorySeo' => [
                Roles::ROLE_CATEGORY_SEO_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'seo' . static::MENU_ITEM_PATH_SEPARATOR . 'unusedFriendlyUrlList' => [
                Roles::ROLE_FRIENDLY_URL_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'contact_form_settings' => [
                Roles::ROLE_CONTACT_FORM_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'stocks' => [
                Roles::ROLE_STOCK_VIEW,
            ],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'constants' => [
                Roles::ROLE_LANGUAGE_CONSTANTS_VIEW,
            ],
            'integrations' . static::MENU_ITEM_PATH_SEPARATOR . 'feeds' => [
                Roles::ROLE_FEED_VIEW,
            ],
            'integrations' . static::MENU_ITEM_PATH_SEPARATOR . 'heureka' => [
                Roles::ROLE_HEUREKA_VIEW,
            ],
        ];
    }

    /**
     * @param array<string, string[]> $pagesGrantedRoles
     * @return array<string, string[]>
     */
    protected function getSectionsGrantedRoles(array $pagesGrantedRoles): array
    {
        $sectionsGrantedRoles = [
            'customers' => [],
            'products' => [],
            'pricing' => [],
            'marketing' => [],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'homepage' => [],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'blog' => [],
            'marketing' . static::MENU_ITEM_PATH_SEPARATOR . 'legal' => [],
            'settings' => [],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'identification' => [],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'communication' => [],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'lists' => [],
            'settings' . static::MENU_ITEM_PATH_SEPARATOR . 'seo' => [],
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
