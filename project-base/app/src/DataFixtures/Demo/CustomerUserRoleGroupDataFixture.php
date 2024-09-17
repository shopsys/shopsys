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
    public const ROLE_GROUP_OWNER = 'role_group_owner';
    public const ROLE_GROUP_USER = 'role_group_user';
    public const ROLE_GROUP_LIMITED_USER = 'role_group_limited_user';

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
    public function load(ObjectManager $manager)
    {
        $this->addReferenceForDefaultRoleGroup();

        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = t('User', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $customerUserRoleGroupData->roles = [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE, CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES];

        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($customerUserRoleGroupData);
        $this->addReference(self::ROLE_GROUP_USER, $customerUserRoleGroup);

        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = t('Limited user', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $customerUserRoleGroupData->roles = [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE];

        $customerUserRoleGroup = $this->customerUserRoleGroupFacade->create($customerUserRoleGroupData);
        $this->addReference(self::ROLE_GROUP_LIMITED_USER, $customerUserRoleGroup);
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
}
