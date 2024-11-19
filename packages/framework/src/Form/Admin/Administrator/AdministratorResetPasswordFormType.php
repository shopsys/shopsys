<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdministratorResetPasswordFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'label' => t('Password'),
                    'constraints' => [
                        new Constraints\Regex(['pattern' => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}$/', 'message' => 'Password has to include uppercase letters, lowercase letters, numbers and must be longer than 10 characters.']),
                        new Constraints\NotBlank([
                            'message' => 'Please enter password',
                        ]),
                    ],
                    'attr' => [
                        'icon' => true,
                        'iconTitle' => t(
                            'Password has to include uppercase letters, lowercase letters, numbers and must be longer than 10 characters.',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => t('Password again'),
                ],
                'invalid_message' => 'Passwords do not match',
                'label' => t('Password'),
            ])
            ->add('save', SubmitType::class, [
                'row_attr' => [
                    'class' => 'form-line__side button',
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['administrator'])
            ->setAllowedTypes('administrator', [Administrator::class])
            ->setDefaults([
                'data_class' => AdministratorData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
