<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class AdministratorRoleGroupDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade
     */
    private AdministratorRoleGroupFacade $administratorRoleGroupFacade;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     */
    public function __construct(AdministratorRoleGroupFacade $administratorRoleGroupFacade)
    {
        $this->administratorRoleGroupFacade = $administratorRoleGroupFacade;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $administratorRoleGroupData = new AdministratorRoleGroupData();
        $administratorRoleGroupData->name = 'Blogger';
        $administratorRoleGroupData->roles = ['ROLE_PRODUCT_VIEW', 'ROLE_ARTICLE_FULL', 'ROLE_BLOG_CATEGORY_FULL', 'ROLE_BLOG_ARTICLE_FULL'];

        $this->administratorRoleGroupFacade->create($administratorRoleGroupData);
    }
}
