<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Form\RolesType;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class AdministratorRoleGroupFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter name']),
                new Constraints\Length(
                    ['max' => 100, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                ),
            ],
            'label' => 'Role name',
        ]);
        $builder->add('roles', RolesType::class, [
            'label' => 'Roles',
            'context' => AdminContext::class,
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_administratorrolegroup_list',
            'entity' => $options['administrator_role_group'],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['administrator_role_group'])
            ->setAllowedTypes('administrator_role_group', [AdministratorRoleGroup::class, 'null'])
            ->setDefaults([
                'administrator_role_group' => null,
                'data_class' => AdministratorRoleGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
