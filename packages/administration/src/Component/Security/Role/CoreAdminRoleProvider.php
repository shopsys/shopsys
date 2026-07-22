<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\CoreRoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

/**
 * Provides default platform roles for admin controllers
 */
class CoreAdminRoleProvider implements CoreRoleProviderInterface
{
    #[Override]
    public function getPriority(): int
    {
        return -2;
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return AdminContext::class;
    }

    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        // Those are special roles that are always present in the system.
        $roleCollection->add(new Role(SystemRole::ADMIN, t('Administrator'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::SUPER_ADMIN, t('Super Administrator'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::PUBLIC_ACCESS, t('Public access'), allowOverwrite: false));

        $coreRoles = [
            AdminRoleSectionsProvider::ORDERS_CUSTOMERS => $this->getOrdersAndCustomersRoles(),
            AdminRoleSectionsProvider::PRODUCTS_CATALOG => $this->getProductsAndCatalogRoles(),
            AdminRoleSectionsProvider::MARKETING_PROMOTIONS => $this->getMarketingAndPromotionsRoles(),
            AdminRoleSectionsProvider::CONTENT_MANAGEMENT => $this->getContentManagementRoles(),
            AdminRoleSectionsProvider::SETTINGS_CONFIGURATION => $this->getSettingsAndConfigurationRoles(),
            AdminRoleSectionsProvider::LEGAL_COMPLIANCE => $this->getLegalAndComplianceRoles(),
            AdminRoleSectionsProvider::SEO_MARKETING_TOOLS => $this->getSeoAndMarketingToolsRoles(),
            AdminRoleSectionsProvider::SYSTEM_TOOLS => $this->getSystemToolsRoles(),
        ];

        foreach ($coreRoles as $section => $roles) {
            foreach ($roles as $role) {
                $role->setRoleSection($section);
                $roleCollection->add($role);
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getOrdersAndCustomersRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_ORDER, t('Orders'), [Permission::EDIT, Permission::DELETE]),
            new Role(AdminRoleConstant::ROLE_CUSTOMER, t('Customers'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_SALES_REPRESENTATIVE, t('Sales representatives'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_ORDER_STATUS, t('Order statuses'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_COMPLAINT, t('Complaints'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_COMPLAINT_STATUS, t('Complaint statuses'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_INQUIRY, t('Inquiries'), [Permission::VIEW]),
            new Role(AdminRoleConstant::ROLE_WATCHDOG, t('Watchdogs'), [Permission::VIEW, Permission::DELETE]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getProductsAndCatalogRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_PRODUCT, t('Products'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_TOP_PRODUCT, t('Top products'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_BESTSELLING_PRODUCT, t('Bestselling products'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_FLAG, t('Flags'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_PARAMETER, t('Parameters'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_PARAMETER_GROUP, t('Parameter groups'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_PARAMETER_VALUE, t('Color parameter values'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_UNIT, t('Measurement units'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_CATEGORY, t('Categories'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_TOP_CATEGORY, t('Top categories'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_AUTOCOMPLETE, t('Autocomplete favorites'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_BRAND, t('Brands'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_ADDITIONAL_SERVICE, t('Additional services'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_STOCK, t('Warehouses'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_PRICE_LIST, t('Price lists'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_GIFT_PLAN, t('Gift plans'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getMarketingAndPromotionsRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_NEWSLETTER, t('Newsletter'), [Permission::DELETE]),
            new Role(AdminRoleConstant::ROLE_PROMO_CODE, t('Promo codes'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_ADVERT, t('Adverts'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_SLIDER_ITEM, t('Slider items'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getContentManagementRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_ARTICLE, t('Articles'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_BLOG_CATEGORY, t('Blog category'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_BLOG_ARTICLE, t('Blog article'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_BLOG_ARTICLE_AUTHOR, t('Blog article author'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_NAVIGATION, t('Navigation'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_NOTIFICATION_BAR, t('Notification bar'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSettingsAndConfigurationRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_PRICING_GROUP, t('Pricing groups'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_VAT, t('Vats'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_DOMAIN, t('E-shop identification'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_MAIL_SETTING, t('Mail setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_MAIL_TEMPLATE, t('Mail templates'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_FREE_TRANSPORT_AND_PAYMENT, t('Free transport and payment'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT, t('Transports and payments'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_COUNTRY, t('Countries'), [Permission::CREATE, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_PHONE_PREFIX, t('Phone prefixes'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_STORE, t('Stores'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_CONTACT_FORM, t('Contact form'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_LANGUAGE_CONSTANTS, t('Language constants'), [Permission::EDIT, Permission::DELETE]),
            new Role(AdminRoleConstant::ROLE_CLOSED_DAYS, t('Closed days'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_FILES, t('Files'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getLegalAndComplianceRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_ORDER_SUBMITTED, t('Order submitted page setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_LEGAL_CONDITIONS, t('Legal conditions article setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_PRIVACY_POLICY, t('Privacy policy article setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_PERSONAL_DATA, t('Personal data access pages setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_USER_CONSENT_POLICY, t('User consent policy article setting'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_PRODUCT_REVIEW_POLICY, t('Product review policy article setting'), [Permission::EDIT]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSeoAndMarketingToolsRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_SEO, t('SEO'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_SEO_PAGES, t('SEO pages'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_ROBOTS, t('Robots.txt settings'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_HREFLANG, t('Alternate language settings'), [Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_CATEGORY_SEO, t('Categories extended SEO'), [Permission::FULL]),
            new Role(AdminRoleConstant::ROLE_FRIENDLY_URL, t('Unused friendly URLs'), [Permission::EDIT, Permission::DELETE]),
            new Role(AdminRoleConstant::ROLE_HEUREKA, t('Heureka setting'), [Permission::VIEW, Permission::EDIT]),
            new Role(AdminRoleConstant::ROLE_FEED, t('Feeds'), [Permission::VIEW, Permission::EDIT]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSystemToolsRoles(): array
    {
        return [
            new Role(AdminRoleConstant::ROLE_TRANSFER, t('Transfers'), [Permission::VIEW, Permission::DELETE]),
            new Role(AdminRoleConstant::ROLE_ADMINISTRATOR, t('Administrators'), [Permission::FULL]),
        ];
    }
}
