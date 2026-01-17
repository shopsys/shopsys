<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Form\DataTransformer\RoleRowDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RoleRowType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $role = $options['role'];
        $availablePermissions = $options['available_permissions'];

        if ($role->isSingleRole()) {
            $builder->add($role->getConstant(), CheckboxType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'data-role' => $role->getConstant(),
                    'data-role-name' => $role->getName(),
                ],
            ]);
        } else {
            $availablePermissions = $this->getFilteredPermissionsForRole($role, $availablePermissions);

            foreach ($availablePermissions as $permission) {
                $builder->add($permission->value, CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'data-role' => $role->getConstant(),
                        'data-permission' => $permission->value,
                        'data-role-name' => $role->getName(),
                    ],
                ]);
            }
        }

        $builder->addModelTransformer(new RoleRowDataTransformer(
            $role,
            $availablePermissions,
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['role'] = $options['role'];
        $view->vars['available_permissions'] = $options['available_permissions'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['role', 'available_permissions', 'context']);
        $resolver->setAllowedTypes('role', Role::class);
        $resolver->setAllowedTypes('available_permissions', 'array');
        $resolver->setAllowedTypes('context', 'string');
        $resolver->setDefaults([
            'label' => false,
            'render_form_row' => false,
        ]);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    private function getFilteredPermissionsForRole(Role $role, array $availablePermissions): array
    {
        $rolePermissions = $role->getAvailablePermissions();

        if ($role->shouldIncludeFullPermission()) {
            $rolePermissions[] = Permission::FULL;
        }

        return array_filter(
            $rolePermissions,
            fn (Permission $permission) => in_array($permission, $availablePermissions, true),
        );
    }
}
