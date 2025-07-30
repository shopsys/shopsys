<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorDataFactory;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;

class AdministratorDataFixture extends AbstractReferenceFixture
{
    public const string SUPERADMINISTRATOR = 'administrator_superadministrator';
    public const string ADMINISTRATOR = 'administrator_administrator';

    /**
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \App\Model\Administrator\AdministratorDataFactory $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade $administratorRoleGroupFacade
     */
    public function __construct(
        private readonly AdministratorFacade $administratorFacade,
        private readonly AdministratorDataFactory $administratorDataFactory,
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createAdministratorReference(1, self::SUPERADMINISTRATOR);
        $administrator = $this->createAdministratorReference(2, self::ADMINISTRATOR);
        $this->setRoleAllForAdministrator($administrator);
    }

    /**
     * Administrators are created (with specific ids) in database migration.
     *
     * @param int $administratorId
     * @param string $referenceName
     * @return \App\Model\Administrator\Administrator
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180702111015
     */
    private function createAdministratorReference(int $administratorId, string $referenceName): Administrator
    {
        $administrator = $this->administratorFacade->getById($administratorId);
        $this->addReference($referenceName, $administrator);

        return $administrator;
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    private function setRoleAllForAdministrator(Administrator $administrator): void
    {
        $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);
        $administratorData->roleGroup = $this->administratorRoleGroupFacade->getSystemManagedRoleGroup(SystemRole::ALL);
        $this->administratorFacade->edit($administrator->getId(), $administratorData);
    }
}
