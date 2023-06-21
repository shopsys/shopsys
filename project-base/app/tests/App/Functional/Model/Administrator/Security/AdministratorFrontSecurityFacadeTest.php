<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator\Security;

use App\DataFixtures\Demo\AdministratorDataFixture;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdministratorFrontSecurityFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorFrontSecurityFacade $administratorFrontSecurityFacade;

    /**
     * @inject
     */
    private AdministratorActivityFacade $administratorActivityFacade;

    /**
     * @inject
     */
    protected RequestStack $requestStack;

    public function testIsAdministratorLoggedNot()
    {
        $this->assertFalse($this->administratorFrontSecurityFacade->isAdministratorLogged());
    }

    public function testIsAdministratorLogged()
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);
        $roles = $administrator->getRoles();
        $token = new UsernamePasswordToken(
            $administrator,
            AdministratorFrontSecurityFacade::ADMINISTRATION_CONTEXT,
            $roles,
        );

        $this->requestStack->getSession()->set(
            '_security_' . AdministratorFrontSecurityFacade::ADMINISTRATION_CONTEXT,
            serialize($token),
        );

        $this->administratorActivityFacade->create($administrator, '127.0.0.1');

        $this->assertTrue($this->administratorFrontSecurityFacade->isAdministratorLogged());
    }
}
