<?php

declare(strict_types=1);

namespace App\Form\Front\Customer\User;

use App\Component\Validator\RegexValidationRule;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Form\DeliveryAddressChoiceType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CustomerUserFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['is_company_customer']) {
            $builder
                ->add('firstName', TextType::class, [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter first name']),
                        new Constraints\Length(['max' => 30, 'maxMessage' => 'First name cannot be longer than {{ limit }} characters']),
                        new Constraints\Regex([
                            'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                            'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        ]),
                    ],
                ])
                ->add('lastName', TextType::class, [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter last name']),
                        new Constraints\Length(['max' => 30, 'maxMessage' => 'Last name cannot be longer than {{ limit }} characters']),
                        new Constraints\Regex([
                            'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                            'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        ]),
                    ],
                ]);
        }

        $builder->add('email', EmailType::class, [
            'attr' => ['readonly' => true],
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter email']),
                new Constraints\Length([
                    'max' => 64,
                    'maxMessage' => 'Email cannot be longer than {{ limit }} characters',
                ]),
                new Email(['message' => 'Please enter valid email']),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                    'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                ]),
            ],
        ])
            ->add('telephone', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter telephone number']),
                    new Constraints\Length([
                        'min' => 9,
                        'minMessage' => 'Telephone number cannot be shorter than {{ limit }} characters',
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::TELEPHONE_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla a znak +',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'constraints' => [
                        new Constraints\Length(['min' => CustomerUserPasswordFacade::MINIMUM_PASSWORD_LENGTH, 'minMessage' => 'Password must be at least {{ limit }} characters long']),
                    ],
                ],
                'invalid_message' => 'Passwords do not match',
            ])
            ->add('defaultDeliveryAddress', DeliveryAddressChoiceType::class)
            ->add('newsletterSubscription', CheckboxType::class, [
                'required' => false,
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['is_company_customer'])
            ->setDefaults([
                'data_class' => CustomerUserData::class,
                'attr' => ['novalidate' => 'novalidate'],
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
            ]);
    }
}
