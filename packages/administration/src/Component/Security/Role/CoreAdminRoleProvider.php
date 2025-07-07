<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
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
    /**
     * @return int
     */
    #[Override]
    public function getPriority(): int
    {
        return -1;
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return AdminContext::class;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection $roleCollection
     */
    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        // Those are special roles that are always present in the system.
        $roleCollection->add(new Role(SystemRole::ADMIN, t('Administrator'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::SUPER_ADMIN, t('Super Administrator'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::ALL, t('Full access'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::ALL_VIEW, t('Full access (view only)'), allowOverwrite: false));
        $roleCollection->add(new Role(SystemRole::PUBLIC_ACCESS, t('Public access'), allowOverwrite: false));

        $coreRoles = array_merge(
            $this->getOrdersAndCustomersRoles(),
            $this->getProductsAndCatalogRoles(),
            $this->getMarketingAndPromotionsRoles(),
            $this->getContentManagementRoles(),
            $this->getSettingsAndConfigurationRoles(),
            $this->getLegalAndComplianceRoles(),
            $this->getSeoAndMarketingToolsRoles(),
            $this->getSystemToolsRoles(),
        );

        foreach ($coreRoles as $role) {
            $roleCollection->add($role);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getOrdersAndCustomersRoles(): array
    {
        return [
            new Role('ROLE_ORDER', t('Orders'), [Permission::FULL]),
            new Role('ROLE_CUSTOMER', t('Customers'), [Permission::FULL]),
            new Role('ROLE_SALES_REPRESENTATIVE', t('Sales representatives'), [Permission::FULL]),
            new Role('ROLE_ORDER_STATUS', t('Order statuses'), [Permission::FULL]),
            new Role('ROLE_COMPLAINT', t('Complaints'), [Permission::FULL]),
            new Role('ROLE_COMPLAINT_STATUS', t('Complaint statuses'), [Permission::FULL]),
            new Role('ROLE_INQUIRY', t('Inquiries'), [Permission::VIEW]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getProductsAndCatalogRoles(): array
    {
        return [
            new Role('ROLE_PRODUCT', t('Products'), [Permission::FULL]),
            new Role('ROLE_TOP_PRODUCT', t('Top products'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_BESTSELLING_PRODUCT', t('Bestselling products'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_FLAG', t('Flags'), [Permission::FULL]),
            new Role('ROLE_PARAMETER', t('Parameters'), [Permission::FULL]),
            new Role('ROLE_PARAMETER_GROUP', t('Parameter groups'), [Permission::FULL]),
            new Role('ROLE_PARAMETER_VALUE', t('Color parameter values'), [Permission::FULL]),
            new Role('ROLE_UNIT', t('Units'), [Permission::FULL]),
            new Role('ROLE_CATEGORY', t('Categories'), [Permission::FULL]),
            new Role('ROLE_TOP_CATEGORY', t('Top categories'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_BRAND', t('Brands'), [Permission::FULL]),
            new Role('ROLE_STOCK', t('Warehouses'), [Permission::FULL]),
            new Role('ROLE_PRICE_LIST', t('Price lists'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getMarketingAndPromotionsRoles(): array
    {
        return [
            new Role('ROLE_NEWSLETTER', t('Newsletter'), [Permission::FULL]),
            new Role('ROLE_PROMO_CODE', t('Promo codes'), [Permission::FULL]),
            new Role('ROLE_ADVERT', t('Adverts'), [Permission::FULL]),
            new Role('ROLE_SLIDER_ITEM', t('Slider items'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getContentManagementRoles(): array
    {
        return [
            new Role('ROLE_ARTICLE', t('Articles'), [Permission::FULL]),
            new Role('ROLE_BLOG_CATEGORY', t('Blog category'), [Permission::FULL]),
            new Role('ROLE_BLOG_ARTICLE', t('Blog article'), [Permission::FULL]),
            new Role('ROLE_NAVIGATION', t('Navigation'), [Permission::FULL]),
            new Role('ROLE_NOTIFICATION_BAR', t('Notification bar'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSettingsAndConfigurationRoles(): array
    {
        return [
            new Role('ROLE_PRICING_GROUP', t('Pricing groups'), [Permission::FULL]),
            new Role('ROLE_VAT', t('Vats'), [Permission::FULL]),
            new Role('ROLE_DOMAIN', t('E-shop identification'), [Permission::FULL]),
            new Role('ROLE_SHOP_INFO', t('Operator information'), [Permission::FULL]),
            new Role('ROLE_MAIL_SETTING', t('Mail setting'), [Permission::FULL]),
            new Role('ROLE_MAIL_TEMPLATE', t('Mail templates'), [Permission::FULL]),
            new Role('ROLE_FREE_TRANSPORT_AND_PAYMENT', t('Free transport and payment'), [Permission::FULL]),
            new Role('ROLE_TRANSPORT_AND_PAYMENT', t('Transports and payments'), [Permission::FULL]),
            new Role('ROLE_COUNTRY', t('Countries'), [Permission::FULL]),
            new Role('ROLE_STORE', t('Stores'), [Permission::FULL]),
            new Role('ROLE_CONTACT_FORM', t('Contact form'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_LANGUAGE_CONSTANTS', t('Language constants'), [Permission::FULL]),
            new Role('ROLE_CLOSED_DAYS', t('Closed days'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getLegalAndComplianceRoles(): array
    {
        return [
            new Role('ROLE_ORDER_SUBMITTED', t('Order submitted page setting'), [Permission::FULL]),
            new Role('ROLE_LEGAL_CONDITIONS', t('Legal conditions article setting'), [Permission::FULL]),
            new Role('ROLE_PRIVACY_POLICY', t('Privacy policy article setting'), [Permission::FULL]),
            new Role('ROLE_PERSONAL_DATA', t('Personal data access pages setting'), [Permission::FULL]),
            new Role('ROLE_USER_CONSENT_POLICY', t('User consent policy article setting'), [Permission::FULL]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSeoAndMarketingToolsRoles(): array
    {
        return [
            new Role('ROLE_SEO', t('SEO'), [Permission::FULL]),
            new Role('ROLE_CATEGORY_SEO', t('Categories extended SEO'), [Permission::FULL]),
            new Role('ROLE_FRIENDLY_URL', t('Unused friendly URLs'), [Permission::VIEW, Permission::DELETE]),
            new Role('ROLE_HEUREKA', t('Heureka setting'), [Permission::VIEW, Permission::EDIT]),
            new Role('ROLE_FEED', t('Feeds'), [Permission::VIEW, Permission::EDIT]),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getSystemToolsRoles(): array
    {
        return [
            new Role('ROLE_TRANSFER', t('Transfers'), [Permission::VIEW, Permission::DELETE]),
            new Role('ROLE_FILES', t('Files'), [Permission::FULL]),
            new Role('ROLE_WATCHDOG', t('Watchdogs'), [Permission::VIEW, Permission::EDIT, Permission::DELETE]),
            new Role('ROLE_ADMINISTRATOR', t('Administrators'), [Permission::FULL]),
        ];
    }
}
