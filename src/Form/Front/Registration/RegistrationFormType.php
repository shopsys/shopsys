<?php

declare(strict_types=1);

namespace App\Form\Front\Registration;

use App\Component\Validator\RegexValidationRule;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Customer\User\RegistrationData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\HoneyPotType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class RegistrationFormType extends AbstractType
{
    private const VALIDATION_GROUP_COMMON_CUSTOMER = 'commonCustomer';
    private const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';
    private const VALIDATION_GROUP_REGULAR_REGISTRATION = 'regularRegistration';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(Domain $domain, CustomerUserFacade $customerUserFacade)
    {
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('companyCustomer', CheckboxType::class, ['required' => false]);
        $this->buildCommonCustomerPart($builder);
        $this->buildCompanyCustomerPart($builder, $options);
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
                        new Constraints\NotBlank([
                            'message' => 'Please enter password',
                            'groups' => [self::VALIDATION_GROUP_REGULAR_REGISTRATION],
                        ]),
                        new Constraints\Length([
                            'min' => CustomerUserPasswordFacade::MINIMUM_PASSWORD_LENGTH,
                            'minMessage' => 'Password must be at least {{ limit }} characters long',
                            'groups' => [self::VALIDATION_GROUP_REGULAR_REGISTRATION],
                        ]),
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
            ->add('newsletterSubscription', CheckboxType::class, [
                'required' => false,
            ])
            ->add('email2', HoneyPotType::class)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildCommonCustomerPart(FormBuilderInterface $builder): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter first name',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter last name',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_COMMON_CUSTOMER],
                    ]),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildCompanyCustomerPart(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím název společnosti',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('companyNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím IČ',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'min' => 8,
                        'max' => 8,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_NUMBER_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('companyVatNumber', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'min' => 12,
                        'max' => 12,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => $options['domain_id'] === Domain::FIRST_DOMAIN_ID
                            ? RegexValidationRule::COMPANY_CZ_VAT_NUMBER_REGEX
                            : RegexValidationRule::COMPANY_SK_VAT_NUMBER_REGEX,
                        'message' => 'Musí obsahovat pouze čísla a velká písmena',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ]);

        if ($options['domain_id'] === Domain::SECOND_DOMAIN_ID) {
            $builder->add('companyTaxNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím DIČ-2',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'min' => 10,
                        'max' => 10,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_TAX_NUMBER_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ]);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    protected function buildContactInformationFormPart(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Email(['message' => 'Please enter valid email']),
                    new Constraints\Length(['max' => 64, 'maxMessage' => 'Email cannot be longer than {{ limit }} characters']),
                    new UniqueEmail(['message' => 'This email is already registered']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
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
                new Constraints\Length(['max' => 30, 'maxMessage' => 'Street cannot be longer than {{ limit }} characters']),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                    'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                ]),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::STREET_NUMBER_REGEX,
                    'message' => 'Ulice musí obsahovat číslo',
                ]),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::STREET_ALPHABET_REGEX,
                    'message' => 'Ulice musí obsahovat písmena',
                ]),
            ],
        ])
            ->add('city', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím zadejte vaše město']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'City cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('postcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím zadejte vaše poštovní směrovací číslo']),
                    new Constraints\Length(['max' => 5, 'maxMessage' => 'Postcode cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
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
                    'groups' => [self::VALIDATION_GROUP_REGULAR_REGISTRATION],
                ]),
                new NotIdenticalToEmailLocalPart([
                    'password' => 'password',
                    'email' => 'email',
                    'errorPath' => 'password',
                    'message' => 'Password cannot be same as part of email before at sign',
                    'groups' => [self::VALIDATION_GROUP_REGULAR_REGISTRATION],
                ]),
            ],
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                /** @var \App\Model\Customer\User\RegistrationData $registrationData */
                $registrationData = $form->getData();
                $customerInfo = $this->customerUserFacade->getCustomerInfo($registrationData->email, $this->domain->getId());

                if ($customerInfo['exists'] === true) {
                    $companyCustomer = $customerInfo['isCompanyCustomer'];
                } else {
                    $companyCustomer = $registrationData->companyCustomer;
                }

                if ($companyCustomer) {
                    $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
                } else {
                    $validationGroups[] = self::VALIDATION_GROUP_COMMON_CUSTOMER;
                }

                if ($customerInfo['exists'] === false) {
                    $validationGroups[] = self::VALIDATION_GROUP_REGULAR_REGISTRATION;
                }

                return $validationGroups;
            },
        ])
        ->addAllowedTypes('domain_id', 'int');
    }
}
