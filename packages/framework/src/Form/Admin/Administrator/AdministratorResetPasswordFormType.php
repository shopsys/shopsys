<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Administrator;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class AdministratorResetPasswordFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                    'label' => 'Password',
                    'constraints' => [
                        new Constraints\Regex(pattern: '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}$/', message: ''),
                        new Constraints\NotBlank(message: ''),
                    ],
                    'attr' => [
                        'data-js-set-password-input' => null,
                    ],
                    'help_attr' => [
                        'data-js-password-help-text' => null,
                    ],
                    'help_html' => true,
                    'help' => t(
                        'Password has to include <span class="text-incorrect" data-js-password-help-text-requirement="upper">uppercase letters</span>, <span class="text-incorrect" data-js-password-help-text-requirement="lower">lowercase letters</span>, <span class="text-incorrect" data-js-password-help-text-requirement="number">numbers</span> and must be <span class="text-incorrect" data-js-password-help-text-requirement="length">longer than 10 characters</span>.',
                    ),
                ],
                'second_options' => [
                    'label' => 'Password again',
                ],
                'invalid_message' => 'Passwords do not match',
                'label' => 'Password',
            ])
            ->add('save', SubmitType::class);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
