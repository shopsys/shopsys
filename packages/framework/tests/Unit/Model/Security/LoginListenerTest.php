<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Security;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
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

    protected function callOnSecurityInteractiveLogin(Administrator $administratorMock): LoginListener
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($administratorMock);

        $administratorActivityFacadeMock = $this->getMockBuilder(AdministratorActivityFacade::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $clockMock = $this->createMock(ClockInterface::class);

        $loginListener = new LoginListener($emMock, $administratorActivityFacadeMock, $clockMock);

        $authenticatorMock = $this->getMockBuilder(AuthenticatorInterface::class)
            ->getMock();

        $passportMock = $this->getMockBuilder(Passport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder(Response::class)
            ->getMock();

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginListener->onSecurityInteractiveLogin(new LoginSuccessEvent($authenticatorMock, $passportMock, $tokenMock, $request, $responseMock, 'test-firewall'));

        return $loginListener;
    }
}
