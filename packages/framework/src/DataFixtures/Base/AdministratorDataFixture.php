<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;

class AdministratorDataFixture extends AbstractReferenceFixture
{
    const SUPERADMINISTRATOR = 'administrator_superadministrator';
    const ADMINISTRATOR = 'administrator_administrator';

    /** @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade */
    private $administratorFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(AdministratorFacade $administratorFacade)
    {
        $this->administratorFacade = $administratorFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $superadminData = new AdministratorData(true);
        $superadminData->username = 'superadmin';
        $superadminData->realName = 'superadmin';
        $superadminData->email = 'no-reply@shopsys.com';
        $superadminData->password = 'admin123';
        $this->createAdministrator($superadminData, self::SUPERADMINISTRATOR);

        $administratorData = new AdministratorData();
        $administratorData->username = 'admin';
        $administratorData->realName = 'admin';
        $administratorData->password = 'admin123';
        $administratorData->email = 'no-reply@shopsys.com';
        $this->createAdministrator($administratorData, self::ADMINISTRATOR);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @param string|null $referenceName
     */
    private function createAdministrator(AdministratorData $administratorData, $referenceName = null)
    {
        $administrator = $this->administratorFacade->create($administratorData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $administrator);
        }
    }
}
