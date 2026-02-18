<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\DataTransformer\RolesGridDataTransformer;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RolesType extends AbstractType
{
    /**
     * @var array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>,\Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider>
     */
    private array $roleSectionsProvidersByContext;

    /**
     * @param iterable<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>,\Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider> $roleSectionsProviders
     */
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly bool $useSimplePermissions,
        #[AutowireIterator('shopsys.role_section_provider', defaultIndexMethod: 'getTargetContext')]
        iterable $roleSectionsProviders = [],
    ) {
        $this->roleSectionsProvidersByContext = iterator_to_array($roleSectionsProviders);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedRoles = $this->getAllowedRoles($options);
        $availablePermissions = $this->getPermissionsToShow($options, $allowedRoles);

        $builder->setAttribute('available_permissions', $availablePermissions);

        $sections = $this->buildGridSections($options['context'], $allowedRoles, $options['grouped']);
        $shouldGroupBySections = count($sections) > 1;

        foreach ($sections as $sectionKey => $roles) {
            $builder->add($sectionKey, RoleSectionType::class, [
                'roles' => $roles,
                'section' => $this->getRoleSection($sectionKey, $options['context']),
                'available_permissions' => $availablePermissions,
                'context' => $options['context'],
                'show_header' => $shouldGroupBySections,
            ]);
        }

        $builder->addModelTransformer(new RolesGridDataTransformer(
            $this->roleRegistry,
            $options['context'],
            $options['excluded_roles'],
            $shouldGroupBySections,
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $availablePermissions */
        $availablePermissions = $form->getConfig()->getAttribute('available_permissions');

        $view->vars['permissions'] = $availablePermissions;
        $view->vars['permission_dependencies'] = $this->getPermissionDependencies($availablePermissions);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'excluded_roles' => SystemRole::getAll(),
            'simple_permissions' => $this->useSimplePermissions,
            'grouped' => true,
            'js_validation' => false,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);

        $resolver->setRequired('context');
        $resolver->setAllowedTypes('context', 'string');
        $resolver->setAllowedTypes('excluded_roles', 'array');
        $resolver->setAllowedTypes('simple_permissions', 'bool');
        $resolver->setAllowedTypes('grouped', 'bool');
    }

    /**
     * @param array<string, mixed> $options
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>
     */
    private function getAllowedRoles(array $options): array
    {
        $roles = $this->roleRegistry->getRoles($options['context']);

        return array_filter(
            $roles,
            fn ($role) => !in_array($role->getConstant(), $options['excluded_roles'], true),
        );
    }

    /**
     * @param array<string, mixed> $options
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $allowedRoles
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission>
     */
    private function getPermissionsToShow(array $options, array $allowedRoles): array
    {
        $reachablePermissions = [];

        foreach ($allowedRoles as $role) {
            $reachablePermissions = array_merge($reachablePermissions, Permission::toValues(...$role->getAvailablePermissions()));
        }

        $reachablePermissions = array_unique($reachablePermissions);

        if ($options['simple_permissions']) {
            $enabledPermissions = [Permission::VIEW->value, Permission::FULL->value];
        } else {
            $enabledPermissions = Permission::toValues(...array_filter(Permission::cases(), fn (Permission $permission) => $permission !== Permission::FULL));
        }

        return Permission::fromValues(...array_intersect($enabledPermissions, $reachablePermissions));
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
     * Build final grid structure based on settings and role count
     *
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     * @return array<string, array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>>
     */
    private function buildGridSections(string $context, array $roles, bool $renderSections): array
    {
        if ($renderSections === false) {
            return [AbstractRoleSectionProvider::OTHER => $roles];
        }

        $roleSections = isset($this->roleSectionsProvidersByContext[$context]) ? $this->roleSectionsProvidersByContext[$context]->getAll() : [];
        $rolesBySection = array_fill_keys(array_keys($roleSections), []);

        foreach ($roles as $role) {
            $section = $role->getRoleSection() ?? AbstractRoleSectionProvider::OTHER;
            $rolesBySection[$section][] = $role;
        }

        return array_filter($rolesBySection, fn (array $rolesInSection) => count($rolesInSection) > 0);
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     */
    private function getRoleSection(
        string $sectionIdentifier,
        string $context,
    ): RoleSection {
        if (isset($this->roleSectionsProvidersByContext[$context]) === false) {
            return AbstractRoleSectionProvider::getDefaultSection();
        }

        return $this->roleSectionsProvidersByContext[$context]->getById($sectionIdentifier);
    }
}
