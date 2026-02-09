<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Collator;
use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;
use Shopsys\FrameworkBundle\Form\DataTransformer\RoleSectionDataTransformer;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RoleSectionType extends AbstractType
{
    public function __construct(
        private readonly RoleRegistryInterface $roleRegistry,
        private readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roles = $options['roles'];
        $permissionsToShow = $options['available_permissions'];

        $collator = new Collator($this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault());
        usort($roles, fn ($a, $b) => $collator->compare($a->getName(), $b->getName()));

        foreach ($roles as $role) {
            $builder->add($role->getConstant(), RoleRowType::class, [
                'role' => $role,
                'available_permissions' => $permissionsToShow,
                'context' => $options['context'],
            ]);
        }

        // Use original unsorted roles for data processing
        $builder->addModelTransformer(new RoleSectionDataTransformer(
            $roles,
            $this->roleRegistry,
            $options['context'],
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['section'] = $options['section'];
        $view->vars['available_permissions'] = $options['available_permissions'];
        $view->vars['show_header'] = $options['show_header'] ?? true;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['roles', 'available_permissions', 'context', 'section']);
        $resolver->setAllowedTypes('roles', 'array');
        $resolver->setAllowedTypes('available_permissions', 'array');
        $resolver->setAllowedTypes('context', 'string');
        $resolver->setAllowedTypes('section', RoleSection::class);
        $resolver->setDefaults([
            'label' => false,
            'render_form_row' => false,
            'show_header' => true,
            'attr' => [
                'class' => 'roles-grid__section',
            ],
        ]);
        $resolver->setAllowedTypes('show_header', 'bool');
    }
}
