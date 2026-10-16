<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Security\AccessControl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\FileManagerAccessChecker;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\User\InMemoryUser;

class FileManagerAccessCheckerTest extends TestCase
{
    private const array ROLE_HIERARCHY = [
        'ROLE_SUPER_ADMIN' => ['ROLE_ALL'],
        'ROLE_ALL' => ['ROLE_PRODUCT_FULL', 'ROLE_NEWSLETTER_DELETE'],
        'ROLE_PRODUCT_FULL' => ['ROLE_PRODUCT_CREATE', 'ROLE_PRODUCT_EDIT', 'ROLE_PRODUCT_DELETE'],
        'ROLE_PRODUCT_CREATE' => ['ROLE_PRODUCT_VIEW'],
        'ROLE_PRODUCT_EDIT' => ['ROLE_PRODUCT_VIEW'],
        'ROLE_PRODUCT_DELETE' => ['ROLE_PRODUCT_VIEW'],
        'ROLE_NEWSLETTER_DELETE' => ['ROLE_NEWSLETTER_VIEW'],
    ];

    /**
     * @param string[] $grantedRoles
     */
    #[DataProvider('rolesProvider')]
    public function testHasAccess(array $grantedRoles, bool $expectedAccess): void
    {
        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken(new InMemoryUser('admin', null, $grantedRoles), 'administration', $grantedRoles));

        $fileManagerAccessChecker = $this->createChecker($tokenStorage);

        $this->assertSame($expectedAccess, $fileManagerAccessChecker->hasAccess());
    }

    /**
     * @return iterable<string, array{string[], bool}>
     */
    public static function rolesProvider(): iterable
    {
        yield 'edit permission' => [['ROLE_ADMIN', 'ROLE_PRODUCT_EDIT'], true];
        yield 'create permission' => [['ROLE_ADMIN', 'ROLE_PRODUCT_CREATE'], true];
        yield 'full permission includes edit' => [['ROLE_ADMIN', 'ROLE_PRODUCT_FULL'], true];
        yield 'super admin' => [['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], true];
        yield 'view permission only' => [['ROLE_ADMIN', 'ROLE_PRODUCT_VIEW'], false];
        yield 'view and delete permissions only' => [['ROLE_ADMIN', 'ROLE_PRODUCT_VIEW', 'ROLE_NEWSLETTER_DELETE'], false];
        yield 'no permission' => [['ROLE_ADMIN'], false];
        yield 'single role whose name only ends with _EDIT' => [['ROLE_ADMIN', 'ROLE_QUICK_EDIT'], false];
        yield 'unregistered role whose name ends with _CREATE' => [['ROLE_ADMIN', 'ROLE_UNKNOWN_CREATE'], false];
    }

    public function testNoAccessWithoutToken(): void
    {
        $fileManagerAccessChecker = $this->createChecker(new TokenStorage());

        $this->assertFalse($fileManagerAccessChecker->hasAccess());
    }

    private function createChecker(TokenStorage $tokenStorage): FileManagerAccessChecker
    {
        $roleRegistry = $this->createStub(RoleRegistryInterface::class);
        $roleRegistry->method('getRoles')->willReturn([
            new Role('ROLE_PRODUCT', 'Products', [Permission::FULL]),
            new Role('ROLE_NEWSLETTER', 'Newsletter', [Permission::DELETE]),
            new Role('ROLE_QUICK_EDIT', 'Single role without permissions'),
        ]);

        return new FileManagerAccessChecker($tokenStorage, new RoleHierarchy(self::ROLE_HIERARCHY), $roleRegistry);
    }
}
