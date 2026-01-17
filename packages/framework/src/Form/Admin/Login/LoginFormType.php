<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Login;

use Override;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class LoginFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter login']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter password']),
                ],
            ])
            ->add('login', SubmitType::class);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'admin_login_form';
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Administrator::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
