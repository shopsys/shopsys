<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\Type\RolesType;
use App\Model\Administrator\RoleGroup\AdministratorRoleGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdministratorRoleGroupFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter name']),
                new Constraints\Length(
                    ['max' => 100, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                ),
            ],
            'label' => t('Role name'),
        ]);
        $builder->add('roles', RolesType::class, [
            'label' => 'Roles',
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AdministratorRoleGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
