<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;

class AdminRoleSectionsProvider extends AbstractRoleSectionProvider
{
    public const string SYSTEM = 'system';
    public const string ORDERS_CUSTOMERS = 'orders_customers';
    public const string PRODUCTS_CATALOG = 'products_catalog';
    public const string MARKETING_PROMOTIONS = 'marketing_promotions';
    public const string CONTENT_MANAGEMENT = 'content_management';
    public const string SETTINGS_CONFIGURATION = 'settings_configuration';
    public const string LEGAL_COMPLIANCE = 'legal_compliance';
    public const string SEO_MARKETING_TOOLS = 'seo_marketing_tools';
    public const string SYSTEM_TOOLS = 'system_tools';

    #[Override]
    protected function defineSections(): void
    {
        $this->addSection(new RoleSection(self::SYSTEM, t('System'), 10, 'gear'));
        $this->addSection(new RoleSection(self::ORDERS_CUSTOMERS, t('Orders & Customers'), 20, 'cart'));
        $this->addSection(new RoleSection(self::PRODUCTS_CATALOG, t('Products & Catalog'), 30, 'tag'));
        $this->addSection(new RoleSection(self::MARKETING_PROMOTIONS, t('Marketing & Promotions'), 40, 'light-bulb'));
        $this->addSection(new RoleSection(self::CONTENT_MANAGEMENT, t('Content Management'), 50, 'pencil'));
        $this->addSection(new RoleSection(self::SETTINGS_CONFIGURATION, t('Settings & Configuration'), 60, 'gear'));
        $this->addSection(new RoleSection(self::LEGAL_COMPLIANCE, t('Legal & Compliance'), 70, 'info'));
        $this->addSection(new RoleSection(self::SEO_MARKETING_TOOLS, t('SEO & Marketing Tools'), 80, 'search'));
        $this->addSection(new RoleSection(self::SYSTEM_TOOLS, t('System Tools'), 90, 'puzzle'));
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
