<?php

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class RouteCsrfProtectorTest extends TestCase
{
    public function testSubRequest(): void
    {
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            new ControllerProtected(),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($event);

        // test is expecting exception is not thrown and assert true suppress warning about no assertions
        $this->assertTrue(true);
    }

    public function testRequestWithoutProtection(): void
    {
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            new ControllerNotProtected(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($event);

        // test is expecting exception is not thrown and assert true suppress warning about no assertions
        $this->assertTrue(true);
    }

    public function testRequestWithProtection(): void
    {
        $validCsrfToken = 'validCsrfToken';
        $request = new Request(
            [
                RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $validCsrfToken,
            ],
            [],
            ['_route' => 'someRouteName']
        );
        $annotationReader = new AnnotationReader();
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
            $this->createMock(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($event);
    }

    public function testRequestWithProtectionWithoutCsrfToken(): void
    {
        $request = new Request();
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);

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
            ['_route' => 'someRouteName']
        );
        $annotationReader = new AnnotationReader();
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
            $this->createMock(HttpKernelInterface::class),
            new ControllerProtected(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
        );

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);

        $this->expectException(BadRequestHttpException::class);
        $routeCsrfProtector->onKernelController($event);
    }
}
