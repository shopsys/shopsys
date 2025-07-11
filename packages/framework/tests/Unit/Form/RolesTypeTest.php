<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleSection;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\DataTransformer\RolesGridDataTransformer;
use Shopsys\FrameworkBundle\Form\RolesType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesTypeTest extends TestCase
{
    private RolesType $formType;

    private RoleRegistryInterface&MockObject $roleRegistry;

    #[Override]
    protected function setUp(): void
    {
        $this->roleRegistry = $this->createMock(RoleRegistryInterface::class);
        $this->formType = new RolesType($this->roleRegistry, false, RoleSection::class);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->formType->configureOptions($resolver);

        $resolvedOptions = $resolver->resolve([]);

        $this->assertSame(AdminContext::class, $resolvedOptions['context']);
        $this->assertFalse($resolvedOptions['label']);
        $this->assertSame(SystemRole::getAll(), $resolvedOptions['excluded_roles']);
        $this->assertFalse($resolvedOptions['simple_permissions']);
        $this->assertFalse($resolvedOptions['js_validation']);
        $this->assertSame(['novalidate' => 'novalidate'], $resolvedOptions['attr']);
    }

    public function testConfigureOptionsWithCustomValues(): void
    {
        $resolver = new OptionsResolver();
        $this->formType->configureOptions($resolver);

        $resolvedOptions = $resolver->resolve([
            'context' => 'TestContext',
            'excluded_roles' => ['ROLE_TEST'],
            'simple_permissions' => true,
        ]);

        $this->assertSame('TestContext', $resolvedOptions['context']);
        $this->assertSame(['ROLE_TEST'], $resolvedOptions['excluded_roles']);
        $this->assertTrue($resolvedOptions['simple_permissions']);
    }

    public function testBuildFormCreatesNestedStructure(): void
    {
        $role1 = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW, Permission::EDIT]);
        $role2 = $this->createMockRole('ROLE_PRODUCT', 'Products', [Permission::VIEW, Permission::FULL]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role1, $role2]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $roleBuilder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('create')
            ->willReturn($roleBuilder);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with($roleBuilder);

        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->isInstanceOf(RolesGridDataTransformer::class));

        $roleBuilder->expects($this->exactly(6)) // 3 permissions × 2 roles (VIEW, EDIT, FULL for each role)
            ->method('add')
            ->with(
                $this->logicalOr('VIEW', 'EDIT', 'FULL'),
                CheckboxType::class,
                $this->callback(
                    fn ($options) => $options['label'] === false &&
                    $options['required'] === false &&
                    isset($options['attr']['data-role']) &&
                    isset($options['attr']['data-permission']),
                ),
            );

        $this->formType->buildForm($builder, [
            'context' => AdminContext::class,
            'excluded_roles' => [SystemRole::SUPER_ADMIN],
            'simple_permissions' => false,
        ]);
    }

    public function testBuildFormWithSimplePermissions(): void
    {
        $role = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW, Permission::FULL]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $roleBuilder = $this->createMock(FormBuilderInterface::class);

        $builder->method('create')->willReturn($roleBuilder);
        $builder->method('add');
        $builder->method('addModelTransformer');

        $roleBuilder->expects($this->exactly(3)) // VIEW, FULL from simple permissions + FULL from shouldIncludeFullPermission
            ->method('add')
            ->with(
                $this->logicalOr('VIEW', 'FULL'),
                CheckboxType::class,
                $this->anything(),
            );

        $this->formType->buildForm($builder, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => true,
        ]);
    }

    public function testBuildFormExcludesExcludedRoles(): void
    {
        $role1 = $this->createMockRole('ROLE_SUPER_ADMIN', 'Super Admin', [Permission::FULL]);
        $role2 = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role1, $role2]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $roleBuilder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once()) // Only one role should be processed
            ->method('create')
            ->with('ROLE_ORDER', FormType::class)
            ->willReturn($roleBuilder);

        $builder->method('add');
        $builder->method('addModelTransformer');
        $roleBuilder->method('add');

        $this->formType->buildForm($builder, [
            'context' => AdminContext::class,
            'excluded_roles' => ['ROLE_SUPER_ADMIN'],
            'simple_permissions' => false,
        ]);
    }

    public function testBuildView(): void
    {
        $role = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW, Permission::EDIT]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $options = [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ];

        $this->formType->buildView($view, $form, $options);

        $this->assertArrayHasKey('grid_structure', $view->vars);
        $this->assertArrayHasKey('permissions', $view->vars);
        $this->assertArrayHasKey('permission_dependencies', $view->vars);

        $this->assertSame(['VIEW', 'CREATE', 'EDIT', 'DELETE', 'FULL'], $view->vars['permissions']);
        $this->assertArrayHasKey('sections', $view->vars['grid_structure']);
        // Role should be in OTHER section since we didn't override the mock
        $this->assertArrayHasKey(RoleSection::OTHER, $view->vars['grid_structure']['sections']);
        $this->assertContains($role, $view->vars['grid_structure']['sections'][RoleSection::OTHER]['roles']);
    }

    public function testBuildViewWithSimplePermissions(): void
    {
        $role = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW, Permission::FULL]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $options = [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => true,
        ];

        $this->formType->buildView($view, $form, $options);

        $this->assertSame(['VIEW', 'FULL'], $view->vars['permissions']);
    }

    public function testGetPermissionDependencies(): void
    {
        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($view, $form, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);

        $dependencies = $view->vars['permission_dependencies'];

        $this->assertArrayHasKey('dependsOn', $dependencies);
        $this->assertArrayHasKey('dependents', $dependencies);

        // FULL should depend on all other permissions
        $this->assertSame(['EDIT', 'VIEW', 'CREATE', 'DELETE'], $dependencies['dependsOn']['FULL']);

        // VIEW should be depended on by other permissions
        $this->assertContains('FULL', $dependencies['dependents']['VIEW']);
        $this->assertContains('EDIT', $dependencies['dependents']['VIEW']);
        $this->assertContains('CREATE', $dependencies['dependents']['VIEW']);
        $this->assertContains('DELETE', $dependencies['dependents']['VIEW']);
    }

    public function testBuildFormWithEmptyRolesList(): void
    {
        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([]);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->never())
            ->method('create');

        $builder->expects($this->never())
            ->method('add');

        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->isInstanceOf(RolesGridDataTransformer::class));

        $this->formType->buildForm($builder, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);
    }

    public function testBuildFormWithAllRolesExcluded(): void
    {
        $role = $this->createMockRole('ROLE_SUPER_ADMIN', 'Super Admin', [Permission::FULL]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->never())
            ->method('create');

        $builder->expects($this->never())
            ->method('add');

        $builder->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->isInstanceOf(RolesGridDataTransformer::class));

        $this->formType->buildForm($builder, [
            'context' => AdminContext::class,
            'excluded_roles' => ['ROLE_SUPER_ADMIN'],
            'simple_permissions' => false,
        ]);
    }

    public function testBuildViewWithEmptyRolesList(): void
    {
        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($view, $form, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);

        $this->assertSame(['sections' => []], $view->vars['grid_structure']);
        $this->assertSame(['VIEW', 'CREATE', 'EDIT', 'DELETE', 'FULL'], $view->vars['permissions']);
    }

    public function testBuildViewGridStructure(): void
    {
        $role = $this->createMockRole('ROLE_ORDER', 'Orders', [Permission::VIEW, Permission::EDIT]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($view, $form, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);

        $gridStructure = $view->vars['grid_structure'];
        $this->assertArrayHasKey('sections', $gridStructure);
        $this->assertArrayHasKey(RoleSection::OTHER, $gridStructure['sections']);
        $section = $gridStructure['sections'][RoleSection::OTHER];
        $this->assertContains($role, $section['roles']);
        $this->assertEquals(t('Other'), $section['name']);
    }

    public function testRoleSortingAlphabetically(): void
    {
        $role1 = $this->createMockRole('ROLE_ZEBRA', 'Zebra Management', [Permission::VIEW]);
        $role2 = $this->createMockRole('ROLE_APPLE', 'Apple Management', [Permission::VIEW]);
        $role3 = $this->createMockRole('ROLE_BANANA', 'Banana Management', [Permission::VIEW]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role1, $role2, $role3]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($view, $form, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);

        $gridStructure = $view->vars['grid_structure'];
        $this->assertArrayHasKey('sections', $gridStructure);
        $this->assertArrayHasKey(RoleSection::OTHER, $gridStructure['sections']);
        $roles = $gridStructure['sections'][RoleSection::OTHER]['roles'];

        // Should be sorted alphabetically by role name, not constant
        $this->assertSame($role2, $roles[0]); // Apple
        $this->assertSame($role3, $roles[1]); // Banana
        $this->assertSame($role1, $roles[2]); // Zebra
    }

    public function testCustomContext(): void
    {
        $customContext = 'CustomContext';
        $role = $this->createMockRole('ROLE_CUSTOM', 'Custom Role', [Permission::VIEW]);
        $role->method('getRoleSection')->willReturn(RoleSection::OTHER);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with($customContext)
            ->willReturn([$role]);

        $view = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($view, $form, [
            'context' => $customContext,
            'excluded_roles' => [],
            'simple_permissions' => false,
        ]);

        $this->assertArrayHasKey('sections', $view->vars['grid_structure']);
        $this->assertArrayHasKey(RoleSection::OTHER, $view->vars['grid_structure']['sections']);
        $this->assertContains($role, $view->vars['grid_structure']['sections'][RoleSection::OTHER]['roles']);
    }

    public function testGetPermissionDependenciesWithSimplePermissions(): void
    {
        $role = $this->createMockRole('ROLE_PRODUCT', 'Product', [Permission::VIEW, Permission::EDIT, Permission::CREATE, Permission::DELETE, Permission::FULL]);

        $this->roleRegistry->expects($this->once())
            ->method('getRoles')
            ->with(AdminContext::class)
            ->willReturn([$role]);

        $formView = new FormView();
        $form = $this->createMock(FormInterface::class);

        $this->formType->buildView($formView, $form, [
            'context' => AdminContext::class,
            'excluded_roles' => [],
            'simple_permissions' => true, // Only VIEW and FULL will be shown
        ]);

        $dependencies = $formView->vars['permission_dependencies'];

        // In simple permissions mode, only VIEW and FULL are available
        // FULL depends on VIEW, but EDIT/CREATE/DELETE are not available
        $expected = [
            'dependsOn' => [
                'FULL' => ['VIEW'], // FULL depends on VIEW (only available dependency)
            ],
            'dependents' => [
                'VIEW' => ['FULL'], // VIEW is depended on by FULL
            ],
        ];

        $this->assertSame($expected, $dependencies);
    }

    /**
     * @param string $constant
     * @param string $name
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockRole(string $constant, string $name, array $availablePermissions): Role
    {
        $role = $this->createMock(Role::class);
        $role->method('getConstant')->willReturn($constant);
        $role->method('getName')->willReturn($name);
        $role->method('getAvailablePermissions')->willReturn($availablePermissions);
        $role->method('getRoleSection')->willReturn(RoleSection::OTHER);
        $role->method('shouldIncludeFullPermission')->willReturn(true);

        return $role;
    }
}
