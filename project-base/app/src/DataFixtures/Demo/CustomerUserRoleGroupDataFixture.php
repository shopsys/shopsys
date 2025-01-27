<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;

class CustomerUserRoleGroupDataFixture extends AbstractReferenceFixture
{
    public const string ROLE_GROUP_OWNER = 'role_group_owner';
    public const string ROLE_GROUP_USER = 'role_group_user';
    public const string ROLE_GROUP_LIMITED_USER = 'role_group_limited_user';
    public const string ROLE_GROUP_CATALOG_USER = 'role_group_catalog_user';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade
     */
    public function __construct(
        private readonly CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory,
        private readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->addReferenceForDefaultRoleGroup();
        $this->createUserRoleGroup();
        $this->createLimitedUserRoleGroup();
        $this->createCatalogUserRoleGroup();
    }

    /**
     * The default role group is created in database migration.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20240711100044
     */
    private function addReferenceForDefaultRoleGroup(): void
    {
        $defaultCustomerUserRoleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();
        $this->addReference(self::ROLE_GROUP_OWNER, $defaultCustomerUserRoleGroup);
    }

    private function createUserRoleGroup(): void
    {
        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = t('User', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $customerUserRoleGroupData->roles = [
            CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE,
            CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES,
            CustomerUserRole::ROLE_API_ORDER_FULL,
        ];

        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($customerUserRoleGroupData);
        $this->addReference(self::ROLE_GROUP_USER, $customerUserRoleGroup);
    }

    private function createLimitedUserRoleGroup(): void
    {
        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = t('Limited user', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $customerUserRoleGroupData->roles = [
            CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE,
            CustomerUserRole::ROLE_API_ORDER_FULL,
        ];

        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($customerUserRoleGroupData);
        $this->addReference(self::ROLE_GROUP_LIMITED_USER, $customerUserRoleGroup);
    }

    private function createCatalogUserRoleGroup(): void
    {
        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = t('Catalog user', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $customerUserRoleGroupData->roles = [
            CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE,
        ];

        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($customerUserRoleGroupData);
        $this->addReference(self::ROLE_GROUP_CATALOG_USER, $customerUserRoleGroup);
    }
}
