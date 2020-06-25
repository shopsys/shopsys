<?php

declare(strict_types=1);

namespace App\Form\Front\Customer\Password;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NewPasswordFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter password']),
                        new Constraints\Length(['min' => CustomerUserPasswordFacade::MINIMUM_PASSWORD_LENGTH, 'minMessage' => 'Password must be at least {{ limit }} characters long']),
                    ],
                ],
                'invalid_message' => 'Passwords do not match',
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
