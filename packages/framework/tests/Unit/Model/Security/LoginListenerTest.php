<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Security;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Shopsys\FrameworkBundle\Model\Security\LoginListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListenerTest extends TestCase
{
    public function testOnSecurityInteractiveLoginTimeLimit(): void
    {
        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->once())->method('setLastActivity');

        $this->callOnSecurityInteractiveLogin($administratorMock);
    }

    public function testOnSecurityInteractiveLoginAdministrator(): void
    {
        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->once())->method('setLoginToken');

        $this->callOnSecurityInteractiveLogin($administratorMock);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administratorMock
     * @return \Shopsys\FrameworkBundle\Model\Security\LoginListener
     */
    protected function callOnSecurityInteractiveLogin(Administrator $administratorMock): LoginListener
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($administratorMock);

        $orderFlowFacadeMock = $this->getMockBuilder(OrderFlowFacade::class)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $administratorActivityFacadeMock = $this->getMockBuilder(AdministratorActivityFacade::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $loginListener = new LoginListener($emMock, $orderFlowFacadeMock, $administratorActivityFacadeMock);

        $authenticatorMock = $this->getMockBuilder(AuthenticatorInterface::class)
            ->getMock();

        $passportMock = $this->getMockBuilder(Passport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder(Response::class)
            ->getMock();

        $loginListener->onSecurityInteractiveLogin(new LoginSuccessEvent($authenticatorMock, $passportMock, $tokenMock, new Request(), $responseMock, 'test-firewall'));

        return $loginListener;
    }
}
