<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\RoleGroup;

use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('names', LocalizedType::class, [
            'required' => true,
            'label' => t('Role name'),
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
            'label' => 'Roles',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->customerUserRole->getAvailableRoles(),
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CustomerUserRoleGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
