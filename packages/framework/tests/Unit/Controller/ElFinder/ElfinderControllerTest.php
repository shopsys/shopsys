<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Controller\ElFinder;

use FM\ElfinderBundle\Loader\ElFinderLoaderInterface;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Controller\ElFinder\ElfinderController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ElfinderControllerTest extends TestCase
{
    private const array ROLES_GRANTING_ACCESS = ['ROLE_PRODUCT_CREATE', 'ROLE_PRODUCT_EDIT'];

    public function testLoadRequiresRoleWithCreateOrEditPermission(): void
    {
        $accessChecker = $this->createMock(AccessCheckerInterface::class);
        $accessChecker->expects($this->once())->method('denyUnlessHasAnyRole')->with(self::ROLES_GRANTING_ACCESS);

        $loader = $this->createStub(ElFinderLoaderInterface::class);
        $loader->method('load')->willReturn(['cwd' => ['name' => 'wysiwyg']]);

        $response = $this->load($this->createController($accessChecker, $loader));

        $this->assertSame(
            ['cwd' => ['name' => 'wysiwyg']],
            Json::decode((string)$response->getContent(), forceArrays: true),
        );
    }

    public function testShowIsDeniedWithoutAccess(): void
    {
        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController(
            $this->createDenyingAccessChecker(),
            $this->createStub(ElFinderLoaderInterface::class),
        );

        $controller->show(new Request(), 'default', '');
    }

    public function testLoadIsDeniedWithoutAccess(): void
    {
        $loader = $this->createMock(ElFinderLoaderInterface::class);
        $loader->expects($this->never())->method('load');

        $this->expectException(AccessDeniedException::class);

        $this->load($this->createController($this->createDenyingAccessChecker(), $loader));
    }

    private function load(ElfinderController $controller): JsonResponse
    {
        return $controller->load(
            $this->createStub(SessionInterface::class),
            $this->createStub(HttpKernelInterface::class),
            new EventDispatcher(),
            new Request(['cmd' => 'open', 'target' => '']),
            'default',
            '',
        );
    }

    private function createDenyingAccessChecker(): AccessCheckerInterface
    {
        $accessChecker = $this->createStub(AccessCheckerInterface::class);
        $accessChecker->method('denyUnlessHasAnyRole')->willThrowException(new AccessDeniedException());

        return $accessChecker;
    }

    private function createController(
        AccessCheckerInterface $accessChecker,
        ElFinderLoaderInterface $loader,
    ): ElfinderController {
        $roleRegistry = $this->createStub(RoleRegistryInterface::class);
        $roleRegistry->method('getRoleIdentifiersWithAnyPermission')->willReturnMap([
            [AdminContext::class, [Permission::CREATE, Permission::EDIT], self::ROLES_GRANTING_ACCESS],
        ]);

        return new ElfinderController(
            $this->createStub(Environment::class),
            ['assets_path' => '', 'instances' => []],
            $loader,
            $accessChecker,
            $roleRegistry,
        );
    }
}
