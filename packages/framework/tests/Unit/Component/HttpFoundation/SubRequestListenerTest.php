<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\TooManyRedirectResponsesException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SubRequestListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubRequestListenerTest extends TestCase
{
    public function getResponseMock(): Response
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['isRedirection', 'send'])
            ->getMock();
        $responseMock->expects($this->once())->method('isRedirection')->willReturn(false);
        $responseMock->expects($this->never())->method('send');

        return $responseMock;
    }

    public function getRedirectResponseMock(bool $send = false): RedirectResponse
    {
        $responseMock = $this->getMockBuilder(RedirectResponse::class)
            ->onlyMethods(['isRedirection', 'send'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects($this->once())->method('isRedirection')->willReturn(true);
        $responseMock->expects($send ? $this->once() : $this->never())->method('send');

        return $responseMock;
    }

    public function testOnKernelResponseOneMasterResponse(): void
    {
        $event = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event);
    }

    public function testOnKernelResponseManyRedirectResponses(): void
    {
        $event1 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getRedirectResponseMock(),
        );

        $event2 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock(),
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event1);
        $subRequestListener->onKernelResponse($event2);

        $this->expectException(TooManyRedirectResponsesException::class);

        $event3 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getRedirectResponseMock(),
        );

        $subRequestListener->onKernelResponse($event3);
    }

    public function testOnKernelResponse(): void
    {
        $event1 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getRedirectResponseMock(true),
        );

        $event2 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock(),
        );

        $event3 = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event1);
        $subRequestListener->onKernelResponse($event2);
        $subRequestListener->onKernelResponse($event3);
    }

    public function testOnKernelController(): void
    {
        // Request cannot be doubled since Symfony 8, its property hooks would require doubling the final InputBag class
        $masterRequest = new Request();
        $masterRequest->setMethod('POST');
        $masterRequest->query->replace([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $masterRequest->request->replace(['post' => 'value']);

        $subRequest = new Request();
        $subRequest->query->replace([
            'key2' => 'value2_2',
            'key3' => 'value3',
        ]);

        $event1 = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            fn () => null,
            $masterRequest,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $event2 = new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            fn () => null,
            $subRequest,
            HttpKernelInterface::SUB_REQUEST,
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelController($event1);
        $subRequestListener->onKernelController($event2);

        $this->assertSame('POST', $subRequest->getMethod());
        $expected = [
            'key1' => 'value1',
            'key2' => 'value2_2',
            'key3' => 'value3',
        ];
        $this->assertSame($expected, $subRequest->query->all());
        $this->assertSame($masterRequest->request, $subRequest->request);
    }
}
