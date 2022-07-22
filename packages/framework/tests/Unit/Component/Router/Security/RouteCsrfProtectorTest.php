<?php

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class RouteCsrfProtectorTest extends TestCase
{
    public function testSubRequest(): void
    {
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $eventMock = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getController'])
            ->getMock();
        $eventMock->expects($this->atLeastOnce())->method('isMasterRequest')->willReturn(false);
        $eventMock->expects($this->never())->method('getController');

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($eventMock);
    }

    public function testRequestWithoutProtection(): void
    {
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $eventMock = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getController', 'getRequest'])
            ->getMock();
        $eventMock->expects($this->atLeastOnce())->method('isMasterRequest')->willReturn(true);
        $eventMock
            ->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn([DummyController::class, 'withoutProtectionAction']);
        $eventMock->expects($this->never())->method('getRequest');

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($eventMock);
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
            ->setMethods(['isTokenValid'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagerMock
            ->expects($this->atLeastOnce())
            ->method('isTokenValid')
            ->with($this->callback(function (CsrfToken $token) use ($validCsrfToken) {
                return $token->getValue() === $validCsrfToken;
            }))
            ->willReturn(true);

        $eventMock = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getController', 'getRequest'])
            ->getMock();
        $eventMock->expects($this->atLeastOnce())->method('isMasterRequest')->willReturn(true);
        $eventMock
            ->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn([DummyController::class, 'withProtectionAction']);
        $eventMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);
        $routeCsrfProtector->onKernelController($eventMock);
    }

    public function testRequestWithProtectionWithoutCsrfToken(): void
    {
        $request = new Request();
        $annotationReader = new AnnotationReader();
        $tokenManagerMock = $this->createMock(CsrfTokenManager::class);

        $eventMock = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getController', 'getRequest'])
            ->getMock();
        $eventMock->expects($this->atLeastOnce())->method('isMasterRequest')->willReturn(true);
        $eventMock
            ->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn([DummyController::class, 'withProtectionAction']);
        $eventMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);

        $this->expectException(BadRequestHttpException::class);
        $routeCsrfProtector->onKernelController($eventMock);
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
            ->setMethods(['isTokenValid'])
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManagerMock
            ->expects($this->atLeastOnce())
            ->method('isTokenValid')
            ->with($this->callback(function (CsrfToken $token) use ($invalidCsrfToken) {
                return $token->getValue() === $invalidCsrfToken;
            }))
            ->willReturn(false);

        $eventMock = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getController', 'getRequest'])
            ->getMock();
        $eventMock->expects($this->atLeastOnce())->method('isMasterRequest')->willReturn(true);
        $eventMock
            ->expects($this->atLeastOnce())
            ->method('getController')
            ->willReturn([DummyController::class, 'withProtectionAction']);
        $eventMock->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);

        $routeCsrfProtector = new RouteCsrfProtector($annotationReader, $tokenManagerMock);

        $this->expectException(BadRequestHttpException::class);
        $routeCsrfProtector->onKernelController($eventMock);
    }
}
