<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use App\Model\Customer\User\RegistrationData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\HoneyPotType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class RegistrationFormType extends AbstractType
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildContactInformationFormPart($builder, $options);
        $this->buildBillingAddressFormPart($builder, $options);

        $builder
            ->add('password', RepeatedType::class, [
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
            ->add('privacyPolicy', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'You have to agree with privacy policy']),
                ],
            ])
            ->add('email2', HoneyPotType::class)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildContactInformationFormPart(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(['max' => 20, 'maxMessage' => 'Phone number cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Email(['message' => 'Please enter valid email']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Email cannot be longer than {{ limit }} characters']),
                    new UniqueEmail(['message' => 'This email is already registered']),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildBillingAddressFormPart(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('street', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím zadejte vaší ulici']),
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'Street cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím zadejte vaše město']),
                    new Constraints\Length(['max' => 100, 'maxMessage' => 'City cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('postcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím zadejte vaše poštovní směrovací číslo']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'Postcode cannot be longer than {{ limit }} characters']),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'domain_id' => $this->domain->getId(),
            'data_class' => RegistrationData::class,
            'attr' => ['novalidate' => 'novalidate'],
            TimedFormTypeExtension::OPTION_ENABLED => true,
            'constraints' => [
                new FieldsAreNotIdentical([
                    'field1' => 'email',
                    'field2' => 'password',
                    'errorPath' => 'password',
                    'message' => 'Password cannot be same as email',
                ]),
                new NotIdenticalToEmailLocalPart([
                    'password' => 'password',
                    'email' => 'email',
                    'errorPath' => 'password',
                    'message' => 'Password cannot be same as part of email before at sign',
                ]),
            ],
        ])
        ->addAllowedTypes('domain_id', 'int');
    }
}
