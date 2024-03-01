<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Security;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\LoginListener;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListenerTest extends TestCase
{
    public function testOnSecurityInteractiveLoginUnique(): void
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->once())->method('flush');

        $userMock = $this->createMock(UniqueLoginInterface::class);
        $userMock->expects($this->once())->method('setLoginToken');

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);

        $loginListener = new LoginListener($emMock, $administratorActivityFacadeMock);
        $loginListener->onSecurityInteractiveLogin(new InteractiveLoginEvent(new Request(), $tokenMock));
    }

    public function testOnSecurityInteractiveLoginTimeLimit(): void
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->atLeastOnce())->method('flush');

        $userMock = $this->createMock(TimelimitLoginInterface::class);
        $userMock->expects($this->once())->method('setLastActivity');

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);

        $loginListener = new LoginListener($emMock, $administratorActivityFacadeMock);
        $loginListener->onSecurityInteractiveLogin(new InteractiveLoginEvent(new Request(), $tokenMock));
    }

    public function testOnSecurityInteractiveLoginResetOrderForm(): void
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->atLeastOnce())->method('flush');

        $userMock = $this->getMockBuilder(CustomerUser::class)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $administratorActivityFacadeMock = $this->createMock(AdministratorActivityFacade::class);

        $loginListener = new LoginListener($emMock, $administratorActivityFacadeMock);
        $loginListener->onSecurityInteractiveLogin(new InteractiveLoginEvent(new Request(), $tokenMock));
    }

    public function testOnSecurityInteractiveLoginAdministrator(): void
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->onlyMethods(['__construct', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->once())->method('flush');

        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->once())->method('setLoginToken');

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->expects($this->once())->method('getUser')->willReturn($administratorMock);

        $administratorActivityFacadeMock = $this->getMockBuilder(AdministratorActivityFacade::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $administratorActivityFacadeMock->expects($this->once())->method('create');

        $loginListener = new LoginListener($emMock, $administratorActivityFacadeMock);
        $loginListener->onSecurityInteractiveLogin(new InteractiveLoginEvent(new Request(), $tokenMock));
    }
}
