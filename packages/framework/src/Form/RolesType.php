<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\DataTransformer\RolesGridDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RolesType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface $roleRegistry
     * @param bool $useSimplePermissions
     */
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly bool $useSimplePermissions,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedRoles = $this->getAllowedRoles($options);
        $permissionsToShow = $this->getPermissionsToShow($options);

        // Build nested form structure: [role][permission] = checkbox
        foreach ($allowedRoles as $role) {
            $roleBuilder = $builder->create($role->getConstant(), FormType::class, [
                'label' => false,
                'required' => false,
            ]);

            $availablePermissions = $this->getFilteredPermissionsForRole($role, $permissionsToShow);

            foreach ($availablePermissions as $permission) {
                $roleBuilder->add($permission->value, CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'data-role' => $role->getConstant(),
                        'data-permission' => $permission->value,
                        'data-role-name' => $role->getName(),
                    ],
                ]);
            }

            $builder->add($roleBuilder);
        }

        $builder->addModelTransformer(new RolesGridDataTransformer(
            $this->roleRegistry,
            $options['context'],
            $options['excluded_roles'],
            $this->getPermissionsToShow($options),
        ));
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $allowedRoles = $this->getAllowedRoles($options);
        $permissionsToShow = $this->getPermissionsToShow($options);

        $view->vars['grid_structure'] = $this->buildGridStructure($allowedRoles);
        $view->vars['permissions'] = array_map(
            fn (Permission $permission) => $permission->value,
            $permissionsToShow,
        );
        $view->vars['permission_dependencies'] = $this->getPermissionDependencies($permissionsToShow);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'context' => AdminContext::class,
            'label' => false,
            'excluded_roles' => SystemRole::getAll(),
            'simple_permissions' => $this->useSimplePermissions,
            'js_validation' => false,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);

        $resolver->setAllowedTypes('context', 'string');
        $resolver->setAllowedTypes('excluded_roles', 'array');
        $resolver->setAllowedTypes('simple_permissions', 'bool');
    }

    /**
     * @param array<string, mixed> $options
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    private function getAllowedRoles(array $options): array
    {
        $roles = $this->roleRegistry->getRoles($options['context']);

        $allowedRoles = array_filter(
            $roles,
            fn ($role) => !in_array($role->getConstant(), $options['excluded_roles'], true),
        );

        usort($allowedRoles, fn ($a, $b) => strcasecmp($a->getName(), $b->getName()));

        return $allowedRoles;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    private function getPermissionsToShow(array $options): array
    {
        if ($options['simple_permissions']) {
            return [Permission::VIEW, Permission::FULL];
        }

        return Permission::cases();
    }

    /**
     * Get permission dependencies - what each permission requires and what depends on it
     *
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $permissions
     * @return array<string, mixed>
     */
    private function getPermissionDependencies(array $permissions): array
    {
        $dependsOn = []; // What each permission requires
        $dependents = []; // What depends on each permission

        foreach ($permissions as $permission) {
            // Only include dependencies that are actually available in the form
            $availableSubordinates = array_filter(
                $permission->getSubordinatePermissions(),
                fn (Permission $sub) => in_array($sub, $permissions, true),
            );

            if (count($availableSubordinates) === 0) {
                continue;
            }

            foreach ($availableSubordinates as $sub) {
                $dependsOn[$permission->value][] = $sub->value;

                if (!isset($dependents[$sub->value])) {
                    $dependents[$sub->value] = [];
                }
                $dependents[$sub->value][] = $permission->value;
            }
        }

        return [
            'dependsOn' => $dependsOn,
            'dependents' => $dependents,
        ];
    }

    /**
     * Build grid structure for template rendering
     *
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     * @return array<string, array<string, mixed>>
     */
    private function buildGridStructure(array $roles): array
    {
        $gridStructure = [];

        foreach ($roles as $role) {
            $gridStructure[$role->getConstant()] = [
                'name' => $role->getName(),
                'constant' => $role->getConstant(),
            ];
        }

        return $gridStructure;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\Role $role
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $permissionsToShow
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    private function getFilteredPermissionsForRole($role, array $permissionsToShow): array
    {
        $rolePermissions = $role->getAvailablePermissions();

        if ($role->shouldIncludeFullPermission()) {
            $rolePermissions[] = Permission::FULL;
        }

        return array_filter(
            $rolePermissions,
            fn (Permission $permission) => in_array($permission, $permissionsToShow, true),
        );
    }
}
