<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Controller\ElFinder;

use FM\ElfinderBundle\Loader\ElFinderLoaderInterface;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Controller\ElFinder\ElfinderController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ElfinderControllerTest extends TestCase
{
    private const array ROLES_WITHOUT_ACCESS = ['ROLE_ADMIN', 'ROLE_PRODUCT_VIEW', 'ROLE_NEWSLETTER_VIEW', 'ROLE_NEWSLETTER_DELETE'];

    /**
     * @param string[] $grantedRoles
     */
    #[DataProvider('accessGrantingRolesProvider')]
    public function testLoadIsAllowedWithCreateOrEditPermission(array $grantedRoles): void
    {
        $loader = $this->createStub(ElFinderLoaderInterface::class);
        $loader->method('load')->willReturn(['cwd' => ['name' => 'wysiwyg']]);

        $response = $this->load($this->createController($grantedRoles, $loader));

        $this->assertSame(
            ['cwd' => ['name' => 'wysiwyg']],
            Json::decode((string)$response->getContent(), forceArrays: true),
        );
    }

    /**
     * @return iterable<string, array{string[]}>
     */
    public static function accessGrantingRolesProvider(): iterable
    {
        yield 'create permission' => [['ROLE_ADMIN', 'ROLE_PRODUCT_CREATE', 'ROLE_PRODUCT_VIEW']];

        yield 'edit permission' => [['ROLE_ADMIN', 'ROLE_PRODUCT_EDIT', 'ROLE_PRODUCT_VIEW']];
    }

    public function testShowIsDeniedWithoutCreateOrEditPermission(): void
    {
        $this->expectException(AccessDeniedException::class);

        $loader = $this->createStub(ElFinderLoaderInterface::class);
        $controller = $this->createController(self::ROLES_WITHOUT_ACCESS, $loader);

        $controller->show(new Request(), 'default', '');
    }

    public function testLoadIsDeniedWithoutCreateOrEditPermission(): void
    {
        $loader = $this->createMock(ElFinderLoaderInterface::class);
        $loader->expects($this->never())->method('load');

        $this->expectException(AccessDeniedException::class);

        $this->load($this->createController(self::ROLES_WITHOUT_ACCESS, $loader));
    }

    private function load(ElfinderController $controller): JsonResponse
    {
        return $controller->load(
            new Session(new MockArraySessionStorage()),
            $this->createStub(HttpKernelInterface::class),
            new EventDispatcher(),
            new Request(['cmd' => 'open', 'target' => '']),
            'default',
            '',
        );
    }

    /**
     * @param string[] $grantedRoles roles already expanded by the role hierarchy
     */
    private function createController(array $grantedRoles, ElFinderLoaderInterface $loader): ElfinderController
    {
        $accessChecker = $this->createStub(AccessCheckerInterface::class);
        $accessChecker->method('denyUnlessHasAnyRole')->willReturnCallback(
            static function (array $roles) use ($grantedRoles): void {
                if (array_intersect($roles, $grantedRoles) === []) {
                    throw new AccessDeniedException('Access denied');
                }
            },
        );

        $roleRegistry = $this->createStub(RoleRegistryInterface::class);
        $roleRegistry->method('getRoles')->willReturn([
            new Role('ROLE_PRODUCT', 'Products', [Permission::FULL]),
            new Role('ROLE_NEWSLETTER', 'Newsletter', [Permission::DELETE]),
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
