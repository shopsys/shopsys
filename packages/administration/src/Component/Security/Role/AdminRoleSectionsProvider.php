<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;

/**
 * The identifiers have to equal the names of the side menu sections, a CRUD controller without a custom role section
 * uses the name of its menu section as the role section.
 */
class AdminRoleSectionsProvider extends AbstractRoleSectionProvider
{
    public const string SYSTEM = 'system';
    public const string SALES = SideMenuBuilder::ROOT_SALES;
    public const string CUSTOMERS = SideMenuBuilder::ROOT_CUSTOMER;
    public const string CATALOG = SideMenuBuilder::ROOT_PRODUCT;
    public const string PRICING = SideMenuBuilder::ROOT_PRICING;
    public const string TRANSPORT_AND_PAYMENT = SideMenuBuilder::ROOT_TRANSPORT_AND_PAYMENT;
    public const string CONTENT = SideMenuBuilder::ROOT_CMS;
    public const string LEGAL = SideMenuBuilder::SECTION_LEGAL;
    public const string SEO = SideMenuBuilder::SECTION_SEO;
    public const string COMMUNICATION = SideMenuBuilder::SECTION_COMMUNICATION;
    public const string INTEGRATIONS = SideMenuBuilder::ROOT_INTEGRATIONS;
    public const string ADMINISTRATORS = SideMenuBuilder::ROOT_ADMINISTRATOR;
    public const string SETTINGS = SideMenuBuilder::ROOT_SETTING;

    #[Override]
    protected function defineSections(): void
    {
        $this->addSection(new RoleSection(self::SYSTEM, t('System'), 10, 'gear'));
        $this->addSection(new RoleSection(self::SALES, t('Sales'), 20, 'document-copy'));
        $this->addSection(new RoleSection(self::CUSTOMERS, t('Customers'), 30, 'person-public'));
        $this->addSection(new RoleSection(self::CATALOG, t('Catalog'), 40, 'cart'));
        $this->addSection(new RoleSection(self::PRICING, t('Prices and discounts'), 50, 'tag'));
        $this->addSection(new RoleSection(self::TRANSPORT_AND_PAYMENT, t('Shipping, payments and stock'), 60, 'truck'));
        $this->addSection(new RoleSection(self::CONTENT, t('Content'), 70, 'chart-piece'));
        $this->addSection(new RoleSection(self::LEGAL, t('Legal'), 80, 'scale'));
        $this->addSection(new RoleSection(self::SEO, t('SEO'), 90, 'search'));
        $this->addSection(new RoleSection(self::COMMUNICATION, t('Communication'), 100, 'letter'));
        $this->addSection(new RoleSection(self::INTEGRATIONS, t('Integrations'), 110, 'puzzle'));
        $this->addSection(new RoleSection(self::ADMINISTRATORS, t('Administrators'), 120, 'person-door-man'));
        $this->addSection(new RoleSection(self::SETTINGS, t('Settings'), 130, 'gear'));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTargetContext(): string
    {
        return AdminContext::class;
    }
}
