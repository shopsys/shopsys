<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Controller\ElFinder;

use FM\ElfinderBundle\Loader\ElFinderLoaderInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\FileManagerAccessChecker;
use Shopsys\FrameworkBundle\Controller\ElFinder\ElfinderController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ElfinderControllerTest extends TestCase
{
    public function testShowIsDeniedWithoutAccess(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->createController(false)->show(new Request(), 'default', '');
    }

    public function testMainJsIsDeniedWithoutAccess(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->createController(false)->mainJS();
    }

    public function testLoadIsDeniedWithoutAccess(): void
    {
        $loader = $this->createMock(ElFinderLoaderInterface::class);
        $loader->expects($this->never())->method('load');

        $this->expectException(AccessDeniedException::class);

        $this->createController(false, $loader)->load(
            new Session(new MockArraySessionStorage()),
            $this->createStub(HttpKernelInterface::class),
            new EventDispatcher(),
            new Request(['cmd' => 'open', 'target' => 'fls1_Y29uZmln']),
            'default',
            '',
        );
    }

    public function testMainJsIsAllowedWithAccess(): void
    {
        $response = $this->createController(true)->mainJS();

        $this->assertSame(200, $response->getStatusCode());
    }

    private function createController(bool $hasAccess, ?ElFinderLoaderInterface $loader = null): ElfinderController
    {
        $fileManagerAccessChecker = $this->createStub(FileManagerAccessChecker::class);
        $fileManagerAccessChecker->method('hasAccess')->willReturn($hasAccess);

        return new ElfinderController(
            $this->createStub(Environment::class),
            ['assets_path' => '', 'instances' => []],
            $loader ?? $this->createStub(ElFinderLoaderInterface::class),
            $fileManagerAccessChecker,
        );
    }
}
