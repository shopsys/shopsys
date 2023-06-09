<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\FragmentHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

class FragmentHandlerTest extends TestCase
{
    public function testRenderNotIgnoreErrorsWithoutDebug()
    {
        $rendererMock = $this->createMock(FragmentRendererInterface::class);
        $rendererMock->expects($this->any())->method('getName')->willReturn('rendererName');
        $rendererMock->expects($this->atLeastOnce())
            ->method('render')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return array_key_exists('ignore_errors', $options) && $options['ignore_errors'] === false;
                }),
            )
            ->willThrowException(new Exception());

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->any())->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererMock);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $debug = false;
        $fragmentHandler = new FragmentHandler($containerMock, $requestStackMock, $debug);
        $fragmentHandler->addRenderer($rendererMock);

        $this->expectException(Exception::class);
        $fragmentHandler->render('uri', 'rendererName', []);
    }

    public function testDeliveryRedirect()
    {
        $response = new Response('', 301);

        $rendererMock = $this->createMock(FragmentRendererInterface::class);
        $rendererMock->expects($this->any())->method('getName')->willReturn('rendererName');
        $rendererMock->expects($this->any())->method('render')->willReturn($response);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->any())->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererMock);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $fragmentHandler = new FragmentHandler($containerMock, $requestStackMock, false);
        $fragmentHandler->addRenderer($rendererMock);

        $this->assertSame('', $fragmentHandler->render('uri', 'rendererName', []));
    }

    public function testNotDeliveryErrorResponse()
    {
        $response = new Response('', 500);

        $rendererMock = $this->createMock(FragmentRendererInterface::class);
        $rendererMock->expects($this->any())->method('getName')->willReturn('rendererName');
        $rendererMock->expects($this->any())->method('render')->willReturn($response);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->expects($this->any())->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererMock);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $fragmentHandler = new FragmentHandler($containerMock, $requestStackMock, false);
        $fragmentHandler->addRenderer($rendererMock);

        $this->expectException(RuntimeException::class);
        $fragmentHandler->render('uri', 'rendererName', []);
    }
}
