<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Role;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Security\Role\AdminRoleHierarchyProvider;
use Shopsys\AdministrationBundle\Component\Security\Role\CoreAdminRoleProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class AdminRoleHierarchyProviderTest extends TestCase
{
    private RoleHierarchy $roleHierarchy;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // the roles are named with t(), which needs the static translator
        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $roleCollection = new RoleCollection();
        (new CoreAdminRoleProvider())->configureRoles($roleCollection);

        $roleRegistry = $this->createStub(RoleRegistryInterface::class);
        $roleRegistry->method('getRoles')->willReturn($roleCollection->all());

        $this->roleHierarchy = new RoleHierarchy((new AdminRoleHierarchyProvider($roleRegistry))->generateRoleHierarchy());
    }

    /**
     * @return iterable<string, array{role: string}>
     */
    public static function getContentEditingRolesData(): iterable
    {
        yield 'article' => [
            'role' => AdminRoleConstant::ROLE_ARTICLE . '_EDIT',
        ];

        yield 'blog article' => [
            'role' => AdminRoleConstant::ROLE_BLOG_ARTICLE . '_EDIT',
        ];

        yield 'mail template' => [
            'role' => AdminRoleConstant::ROLE_MAIL_TEMPLATE . '_EDIT',
        ];
    }

    #[DataProvider('getContentEditingRolesData')]
    public function testContentEditingRoleReachesViewAndCreateOfMediaButNotMore(string $role): void
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames([$role]);

        $this->assertContains(AdminRoleConstant::ROLE_MEDIA . '_VIEW', $reachableRoles);
        $this->assertContains(AdminRoleConstant::ROLE_MEDIA . '_CREATE', $reachableRoles);
        $this->assertNotContains(AdminRoleConstant::ROLE_MEDIA . '_EDIT', $reachableRoles);
        $this->assertNotContains(AdminRoleConstant::ROLE_MEDIA . '_DELETE', $reachableRoles);
        $this->assertContains(preg_replace('~_EDIT$~', '_VIEW', $role), $reachableRoles, 'the rights of the role itself are kept');
    }

    public function testViewOfContentRoleDoesNotReachMedia(): void
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames([AdminRoleConstant::ROLE_ARTICLE . '_VIEW']);

        $this->assertNotContains(AdminRoleConstant::ROLE_MEDIA . '_VIEW', $reachableRoles);
    }
}
