<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;

class AdministratorRoleGroupDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     */
    public function __construct(
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $administratorRoleGroupData = new AdministratorRoleGroupData();
        $administratorRoleGroupData->name = 'Blogger';
        $administratorRoleGroupData->roles = ['ROLE_PRODUCT_VIEW', 'ROLE_ARTICLE_FULL', 'ROLE_BLOG_CATEGORY_FULL', 'ROLE_BLOG_ARTICLE_FULL'];

        $this->administratorRoleGroupFacade->create($administratorRoleGroupData);
    }
}
