<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Security;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticatorTest extends TestCase
{
    public function testCheckLoginProcessWithRequestError()
    {
        $authenticator = $this->getAuthenticator();

        /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);

        $requestMock->expects($this->never())->method('getSession');

        $requestMock->attributes = $this->createMock(ParameterBag::class);
        $requestMock->attributes->expects($this->once())->method('has')->willReturn(true);
        $requestMock->attributes->expects($this->once())->method('get')->willReturn(new stdClass());

        $this->expectException(LoginFailedException::class);
        $authenticator->checkLoginProcess($requestMock);
    }

    public function testCheckLoginProcessWithSessionError()
    {
        $authenticator = $this->getAuthenticator();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->atLeastOnce())->method('get')->willReturn(new stdClass());
        $sessionMock->expects($this->atLeastOnce())->method('remove');

        /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())->method('getSession')->willReturn($sessionMock);

        $requestMock->attributes = $this->createMock(ParameterBag::class);
        $requestMock->attributes->expects($this->once())->method('has')->willReturn(false);
        $requestMock->attributes->expects($this->never())->method('get');

        $this->expectException(LoginFailedException::class);
        $authenticator->checkLoginProcess($requestMock);
    }

    public function testCheckLoginProcessWithoutSessionError()
    {
        $authenticator = $this->getAuthenticator();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('get')->willReturn(null);
        $sessionMock->expects($this->once())->method('remove');

        /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())->method('getSession')->willReturn($sessionMock);

        $requestMock->attributes = $this->createMock(ParameterBag::class);
        $requestMock->attributes->expects($this->once())->method('has')->willReturn(false);
        $requestMock->attributes->expects($this->never())->method('get');

        $this->assertSame(true, $authenticator->checkLoginProcess($requestMock));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Security\Authenticator
     */
    private function getAuthenticator()
    {
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        return new Authenticator($tokenStorageMock, $eventDispatcherMock);
    }
}
