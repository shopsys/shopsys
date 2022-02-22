<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;

class AdministratorDataFixture extends AbstractReferenceFixture
{
    public const SUPERADMINISTRATOR = 'administrator_superadministrator';
    public const ADMINISTRATOR = 'administrator_administrator';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade
     */
    private $administratorFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(AdministratorFacade $administratorFacade)
    {
        $this->administratorFacade = $administratorFacade;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->createAdministratorReference(1, self::SUPERADMINISTRATOR);
        $this->createAdministratorReference(2, self::ADMINISTRATOR);
    }

    /**
     * Administrators are created (with specific ids) in database migration.
     *
     * @param int $administratorId
     * @param string $referenceName
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180702111015
     */
    private function createAdministratorReference(int $administratorId, string $referenceName)
    {
        $administrator = $this->administratorFacade->getById($administratorId);
        $this->addReference($referenceName, $administrator);
    }
}
