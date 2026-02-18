<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class RouteCsrfProtectorTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testSubRequest(): void
    {
        $tokenManagerStub = $this->createStub(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new ControllerProtected(),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($tokenManagerStub, new InMemoryCache());
        $routeCsrfProtector->onKernelController($event);
    }

    #[DoesNotPerformAssertions]
    public function testRequestWithoutProtection(): void
    {
        $tokenManagerStub = $this->createStub(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new ControllerNotProtected(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($tokenManagerStub, new InMemoryCache());
        $routeCsrfProtector->onKernelController($event);
    }

    public function testRequestWithProtection(): void
    {
        $validCsrfToken = 'validCsrfToken';
        $request = new Request(
            [
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $validCsrfToken,
            ],
            [],
            ['_route' => 'someRouteName'],
        );
        $tokenManagerMock = $this->getMockBuilder(CsrfTokenManager::class)
            ->onlyMethods(['isTokenValid'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagerMock
            ->expects($this->atLeastOnce())
            ->method('isTokenValid')
            ->with($this->callback(function (CsrfToken $token) use ($validCsrfToken) {
                return $token->getValue() === $validCsrfToken;
            }))
            ->willReturn(true);

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($tokenManagerMock, new InMemoryCache());
        $routeCsrfProtector->onKernelController($event);
    }

    public function testRequestWithProtectionWithoutCsrfToken(): void
    {
        $request = new Request();
        $tokenManagerStub = $this->createStub(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($tokenManagerStub, new InMemoryCache());

        $this->expectException(BadRequestHttpException::class);
        $routeCsrfProtector->onKernelController($event);
    }

    public function testRequestWithProtectionWithInvalidCsrfToken(): void
    {
        $invalidCsrfToken = 'invalidCsrfToken';
        $request = new Request(
            [
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $invalidCsrfToken,
            ],
            [],
            ['_route' => 'someRouteName'],
        );
        $tokenManagerMock = $this->getMockBuilder(CsrfTokenManager::class)
            ->onlyMethods(['isTokenValid'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagerMock
            ->expects($this->atLeastOnce())
            ->method('isTokenValid')
            ->with($this->callback(function (CsrfToken $token) use ($invalidCsrfToken) {
                return $token->getValue() === $invalidCsrfToken;
            }))
            ->willReturn(false);

        $event = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($tokenManagerMock, new InMemoryCache());

        $this->expectException(BadRequestHttpException::class);
        $routeCsrfProtector->onKernelController($event);
    }
}
