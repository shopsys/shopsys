<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Administrator\Administrator;
use App\Model\Administrator\AdministratorData;
use App\Model\Administrator\AdministratorDataFactory;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorPasswordFacade;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFacade;

class AdministratorDataFixture extends AbstractReferenceFixture
{
    public const string SUPERADMINISTRATOR = 'administrator_superadministrator';
    public const string SUPERVISOR = 'administrator_supervisor';
    public const string ADMINISTRATOR = 'administrator_administrator';
    public const string OPERATOR = 'administrator_operator';

    private const string DEFAULT_PASSWORD = 'admin123';

    /**
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(
        private readonly AdministratorFacade $administratorFacade,
        private readonly AdministratorDataFactory $administratorDataFactory,
        private readonly AdministratorRoleGroupFacade $administratorRoleGroupFacade,
        private readonly AdministratorPasswordFacade $administratorPasswordFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createAdministratorReference(1, self::SUPERADMINISTRATOR);
        $administrator = $this->createAdministratorReference(2, self::ADMINISTRATOR);
        $this->setRoleAllForAdministrator($administrator);

        $supervisorData = $this->createAdministratorData('supervisor', 'Supervisor', 'supervisor@example.com');
        $supervisorData->roles = [SystemRole::SUPER_ADMIN];
        $this->createAdministrator($supervisorData, self::SUPERVISOR);

        $operatorData = $this->createAdministratorData('operator', 'Operator', 'operator@example.com');
        $operatorData->roleGroup = $this->administratorRoleGroupFacade->getSystemManagedRoleGroup(SystemRole::ALL);
        $this->createAdministrator($operatorData, self::OPERATOR);
    }

    private function createAdministratorData(string $username, string $realName, string $email): AdministratorData
    {
        $administratorData = $this->administratorDataFactory->create();
        $administratorData->username = $username;
        $administratorData->realName = $realName;
        $administratorData->email = $email;

        return $administratorData;
    }

    private function createAdministrator(AdministratorData $administratorData, string $referenceName): void
    {
        $administrator = $this->administratorFacade->create($administratorData);
        $this->administratorPasswordFacade->setPassword($administrator, self::DEFAULT_PASSWORD);
        $this->addReference($referenceName, $administrator);
    }

    /**
     * Administrators are created (with specific ids) in database migration.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180702111015
     */
    private function createAdministratorReference(int $administratorId, string $referenceName): Administrator
    {
        $administrator = $this->administratorFacade->getById($administratorId);
        $this->addReference($referenceName, $administrator);

        return $administrator;
    }

    private function setRoleAllForAdministrator(Administrator $administrator): void
    {
        $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);
        $administratorData->roleGroup = $this->administratorRoleGroupFacade->getSystemManagedRoleGroup(SystemRole::ALL);
        $this->administratorFacade->edit($administrator->getId(), $administratorData);
    }
}
