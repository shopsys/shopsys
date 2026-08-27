<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\FragmentHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

class FragmentHandlerTest extends TestCase
{
    public function testRenderNotIgnoreErrorsWithoutDebug(): void
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

        $requestStackStub = $this->createStub(RequestStack::class);
        $requestStackStub->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererMock);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $debug = false;
        $fragmentHandler = new FragmentHandler($containerMock, $requestStackStub, $debug);
        $fragmentHandler->addRenderer($rendererMock);

        $this->expectException(Exception::class);
        $fragmentHandler->render('uri', 'rendererName', []);
    }

    public function testDeliveryRedirect(): void
    {
        $response = new RedirectResponse('https://example.com/', 301);

        $rendererStub = $this->createStub(FragmentRendererInterface::class);
        $rendererStub->method('getName')->willReturn('rendererName');
        $rendererStub->method('render')->willReturn($response);

        $requestStackStub = $this->createStub(RequestStack::class);
        $requestStackStub->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererStub);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $fragmentHandler = new FragmentHandler($containerMock, $requestStackStub, false);
        $fragmentHandler->addRenderer($rendererStub);

        $this->assertSame($response->getContent(), $fragmentHandler->render('uri', 'rendererName', []));
    }

    public function testNotDeliveryPlainRedirectionResponse(): void
    {
        $response = new Response('', 301);

        $rendererStub = $this->createStub(FragmentRendererInterface::class);
        $rendererStub->method('getName')->willReturn('rendererName');
        $rendererStub->method('render')->willReturn($response);

        $requestStackStub = $this->createStub(RequestStack::class);
        $requestStackStub->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererStub);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $fragmentHandler = new FragmentHandler($containerMock, $requestStackStub, false);
        $fragmentHandler->addRenderer($rendererStub);

        $this->expectException(RuntimeException::class);
        $fragmentHandler->render('uri', 'rendererName', []);
    }

    public function testNotDeliveryErrorResponse(): void
    {
        $response = new Response('', 500);

        $rendererStub = $this->createStub(FragmentRendererInterface::class);
        $rendererStub->method('getName')->willReturn('rendererName');
        $rendererStub->method('render')->willReturn($response);

        $requestStackStub = $this->createStub(RequestStack::class);
        $requestStackStub->method('getCurrentRequest')->willReturn(Request::create('/'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->once())->method('get')->willReturn($rendererStub);
        $containerMock->expects($this->once())->method('has')->willReturn(true);

        $fragmentHandler = new FragmentHandler($containerMock, $requestStackStub, false);
        $fragmentHandler->addRenderer($rendererStub);

        $this->expectException(RuntimeException::class);
        $fragmentHandler->render('uri', 'rendererName', []);
    }
}
