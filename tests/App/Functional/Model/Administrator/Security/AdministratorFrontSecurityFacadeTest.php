<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator\Security;

use App\DataFixtures\Demo\AdministratorDataFixture;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AdministratorFrontSecurityFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade
     * @inject
     */
    private $administratorFrontSecurityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade
     * @inject
     */
    private $administratorActivityFacade;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     * @inject
     */
    protected $session;

    public function testIsAdministratorLoggedNot()
    {
        $this->assertFalse($this->administratorFrontSecurityFacade->isAdministratorLogged());
    }

    public function testIsAdministratorLogged()
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $password = '';
        $roles = $administrator->getRoles();
        $token = new UsernamePasswordToken($administrator, $password, AdministratorFrontSecurityFacade::ADMINISTRATION_CONTEXT, $roles);

        $this->session->set('_security_' . AdministratorFrontSecurityFacade::ADMINISTRATION_CONTEXT, serialize($token));

        $this->administratorActivityFacade->create($administrator, '127.0.0.1');

        $this->assertTrue($this->administratorFrontSecurityFacade->isAdministratorLogged());
    }
}
