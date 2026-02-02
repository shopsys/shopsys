<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderWithdrawalFormType extends AbstractType
{
    public const string VALIDATION_GROUP_WITHDRAWAL_REQUIRED = 'withdrawalRequired';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter first name',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter last name',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter email',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                    new Email([
                        'message' => 'Please enter valid email',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                    new Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Email cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_WITHDRAWAL_REQUIRED],
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Phone',
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Phone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Note',
                'required' => false,
            ])
            ->add('requestedAt', DateTimeType::class, [
                'label' => 'Requested at',
                'required' => false,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WithdrawalRequestData::class,
            'label' => false,
        ]);
    }
}
