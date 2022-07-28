<?php

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\TooManyRedirectResponsesException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SubRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SubRequestListenerTest extends TestCase
{
    /**
     * @param bool $redirect
     * @param bool $send
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Response
     */
    public function getResponseMock(bool $redirect = false, bool $send = false): MockObject|Response
    {
        $responseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['isRedirection', 'send'])
            ->getMock();
        $responseMock->expects($this->once())->method('isRedirection')->willReturn($redirect);
        $responseMock->expects($send ? $this->once() : $this->never())->method('send');

        return $responseMock;
    }

    public function testOnKernelResponseOneMasterResponse(): void
    {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event);
    }

    public function testOnKernelResponseManyRedirectResponses(): void
    {
        $event1 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock(true)
        );

        $event2 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock()
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event1);
        $subRequestListener->onKernelResponse($event2);

        $this->expectException(TooManyRedirectResponsesException::class);

        $event3 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock(true)
        );

        $subRequestListener->onKernelResponse($event3);
    }

    public function testOnKernelResponse(): void
    {
        $event1 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock(true, true)
        );

        $event2 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            $this->getResponseMock()
        );

        $event3 = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelResponse($event1);
        $subRequestListener->onKernelResponse($event2);
        $subRequestListener->onKernelResponse($event3);
    }

    public function testOnKernelController(): void
    {
        /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject $masterRequestMock */
        $masterRequestMock = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getMethod'])
            ->getMock();

        $masterRequestMock->expects($this->once())->method('getMethod')->willReturn('POST');
        $masterRequestMock->query->replace([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $masterRequestMock->request->replace(['post' => 'value']);

        /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject $subRequestMock */
        $subRequestMock = $this->getMockBuilder(Request::class)
            ->onlyMethods(['setMethod'])
            ->getMock();
        $subRequestMock->expects($this->once())->method('setMethod')->with($this->equalTo('POST'));
        $subRequestMock->query->replace([
            'key2' => 'value2_2',
            'key3' => 'value3',
        ]);

        $event1 = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            fn () => null,
            $masterRequestMock,
            HttpKernelInterface::MASTER_REQUEST,
        );

        $event2 = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            fn () => null,
            $subRequestMock,
            HttpKernelInterface::SUB_REQUEST,
        );

        $subRequestListener = new SubRequestListener();
        $subRequestListener->onKernelController($event1);
        $subRequestListener->onKernelController($event2);

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2_2',
            'key3' => 'value3',
        ];
        $this->assertSame($expected, $subRequestMock->query->all());
        $this->assertSame($masterRequestMock->request, $subRequestMock->request);
    }
}
