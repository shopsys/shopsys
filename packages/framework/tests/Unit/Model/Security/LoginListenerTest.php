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

        $this->callOnSecurityInteractiveLogin($administratorMock, LoginListener::ADMINISTRATION_FIREWALL);
    }

    public function testOnSecurityInteractiveLoginAdministrator(): void
    {
        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->once())->method('setLoginToken');

        $this->callOnSecurityInteractiveLogin($administratorMock, LoginListener::ADMINISTRATION_FIREWALL);
    }

    public function testOnSecurityInteractiveLoginIgnoresNonAdministrationFirewall(): void
    {
        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->never())->method('setLastActivity');
        $administratorMock->expects($this->never())->method('setLoginToken');

        $this->callOnSecurityInteractiveLogin($administratorMock, 'mcp');
    }

    protected function callOnSecurityInteractiveLogin(
        Administrator $administratorMock,
        string $firewallName,
    ): LoginListener {
        $emStub = $this->createMock(EntityManager::class);
        $emStub->expects($firewallName === LoginListener::ADMINISTRATION_FIREWALL ? $this->once() : $this->never())->method('flush');

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($firewallName === LoginListener::ADMINISTRATION_FIREWALL ? $this->once() : $this->never())->method('getUser')->willReturn($administratorMock);

        $administratorActivityFacadeStub = $this->createMock(AdministratorActivityFacade::class);
        $administratorActivityFacadeStub->expects($firewallName === LoginListener::ADMINISTRATION_FIREWALL ? $this->once() : $this->never())->method('create');

        $clockStub = $this->createStub(ClockInterface::class);

        $loginListener = new LoginListener($emStub, $administratorActivityFacadeStub, $clockStub);

        $authenticatorStub = $this->createStub(AuthenticatorInterface::class);

        $passportStub = $this->createStub(Passport::class);

        $responseStub = $this->createStub(Response::class);

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginListener->onSecurityInteractiveLogin(new LoginSuccessEvent($authenticatorStub, $passportStub, $tokenMock, $request, $responseStub, $firewallName));

        return $loginListener;
    }
}
