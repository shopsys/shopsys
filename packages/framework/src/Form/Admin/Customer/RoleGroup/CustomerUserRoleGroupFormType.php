<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CustomerUserRoleGroupFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole $customerUserRole
     */
    public function __construct(protected readonly CustomerUserRole $customerUserRole)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('names', LocalizedType::class, [
            'required' => true,
            'label' => t('Role group name'),
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter role name']),
                    new Constraints\Length(
                        ['max' => 100, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                    ),
                ],
            ],
        ]);
        $builder->add('roles', ChoiceType::class, [
            'label' => t('Roles'),
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->customerUserRole->getAvailableRoles(),
            'choice_translation_domain' => false,
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_superadmin_customer_user_role_group_list',
            'entity' => $options['customer_user_role_group'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
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
