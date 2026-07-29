<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;

class AdministratorRoleGroupDataFixture extends AbstractReferenceFixture
{
    public function __construct(
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        private readonly AdministratorRoleGroupDataFactory $administratorRoleGroupDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $administratorRoleGroupData = $this->administratorRoleGroupDataFactory->create();
        $administratorRoleGroupData->name = 'Blogger';
        $administratorRoleGroupData->roles = ['ROLE_PRODUCT_VIEW', 'ROLE_ARTICLE_FULL', 'ROLE_BLOG_CATEGORY_FULL', 'ROLE_BLOG_ARTICLE_FULL'];

        $this->administratorRoleGroupFacade->create($administratorRoleGroupData);
    }
}
