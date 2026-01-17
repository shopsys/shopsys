<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\RolesType;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class CustomerUserRoleGroupFormType extends AbstractType
{
    public function __construct(protected readonly RoleRegistryInterface $roleRegistry)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('names', LocalizedType::class, [
            'required' => true,
            'label' => 'Role group name',
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter role name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ],
        ]);

        $builder->add('roles', RolesType::class, [
            'label' => 'Role',
            'context' => FrontendApiContext::class,
            'row_attr' => [
                'data-js-customer-role-group' => null,
            ],
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_superadmin_customer_user_role_group_list',
            'entity' => $options['customer_user_role_group'],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('customer_user_role_group')
            ->setAllowedTypes('customer_user_role_group', [CustomerUserRoleGroup::class, 'null'])
            ->setDefaults([
                'customer_user_role_group' => null,
                'data_class' => CustomerUserRoleGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
