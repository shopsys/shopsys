<?php

declare(strict_types=1);

namespace App\Form\Front\Order;

use App\Component\Validator\RegexValidationRule;
use App\Model\Country\CountryFacade;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Order\FrontOrderData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DeliveryAddressChoiceType;
use Shopsys\FrameworkBundle\Form\Transformers\InverseTransformer;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PersonalInfoFormType extends AbstractType
{
    public const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';
    public const VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS = 'differentDeliveryAddress';
    public const VALIDATION_GROUP_COMMON_CUSTOMER = 'ordinaryCustomer';
    public const VALIDATION_GROUP_REGISTRATION = 'registration';
    public const VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD = 'registrationWithPassword';

    /**
     * @var \App\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade
     */
    private $heurekaFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @param \App\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        CountryFacade $countryFacade,
        HeurekaFacade $heurekaFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->countryFacade = $countryFacade;
        $this->heurekaFacade = $heurekaFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries = [$this->countryFacade->getCountryOnCurrentDomain()];
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

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

        $builder->add('companyCustomer', CheckboxType::class, ['required' => false]);

        $emailOptions = [
            'attr' => [],
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter email']),
                new Email(['message' => 'Please enter valid email']),
                new Constraints\Length(['max' => 64, 'maxMessage' => 'Email cannot be longer than {{ limit }} characters']),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                    'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                ]),
            ],
        ];
        if ($currentCustomerUser !== null) {
            $emailOptions['disabled'] = true;
            $emailOptions['attr']['readonly'] = 'readonly';
            $emailOptions['attr']['disabled'] = 'disabled';
            $emailOptions['data'] = $currentCustomerUser->getEmail();
        }
        $builder
            ->add('email', EmailType::class, $emailOptions)
            ->add('telephone', TextType::class, [
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
            ->add('companyName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter company name',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length(['max' => 30,
                        'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
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
                        'message' => 'Please enter identification number',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'min' => 8,
                        'max' => 8,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_NUMBER_REGEX,
                        'message' => 'Prosím, zadejte pouze čísla',
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
                        'pattern' => RegexValidationRule::COMPANY_VAT_NUMBER_REGEX,
                        'message' => 'Musí obsahovat pouze pouze čísla a velká písmena',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('street', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter street']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'Street name cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_ALPHABET_REGEX,
                        'message' => 'Ulice musí obsahovat písmeno',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_NUMBER_REGEX,
                        'message' => 'Ulice musí obsahovat číslo',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter city']),
                    new Constraints\Length(['max' => 30, 'maxMessage' => 'City name cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('postcode', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter zip code']),
                    new Constraints\Length(['max' => 5, 'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters']),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose country']),
                ],
            ])
            ->add($builder
                ->create('deliveryAddressFilled', CheckboxType::class, [
                    'required' => false,
                    'property_path' => 'deliveryAddressSameAsBillingAddress',
                ])
                ->addModelTransformer(new InverseTransformer()));

        if ($currentCustomerUser !== null) {
            $builder->add('deliveryAddress', DeliveryAddressChoiceType::class, [
                'required' => false,
            ]);
        }

        $builder
            ->add('deliveryFirstName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter first name of contact person',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'First name of contact person cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryLastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter last name of contact person',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Last name of contact person cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryCompanyName', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryTelephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'min' => 9,
                        'minMessage' => 'Telephone number cannot be shorter than {{ limit }} characters',
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::TELEPHONE_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla a znak +',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryStreet', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter street',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_NUMBER_REGEX,
                        'message' => 'Ulice musí obsahovat číslo',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_ALPHABET_REGEX,
                        'message' => 'Ulice musí obsahovat písmena',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryCity', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter city',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryPostcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter zip code',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Length([
                        'max' => 5,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('deliveryCountry', ChoiceType::class, [
                'required' => true,
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please choose country',
                        'groups' => [self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS],
                    ]),
                ],
            ])
            ->add('note', TextareaType::class, ['required' => false])
            ->add('save', SubmitType::class);

        if ($currentCustomerUser === null) {
            $builder->add('register', CheckboxType::class, ['required' => false]);
            $builder->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'constraints' => [
                        new Constraints\NotBlank([
                            'message' => 'Please enter password',
                            'groups' => [self::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD],
                        ]),
                        new Constraints\Length([
                            'min' => 6,
                            'minMessage' => 'Password must be at least {{ limit }} characters long',
                            'groups' => [self::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD],
                        ]),
                    ],
                ],
                'second_options' => [
                    'constraints' => [
                        new Constraints\NotBlank([
                            'message' => 'Please enter password',
                            'groups' => [self::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD],
                        ]),
                        new Constraints\Length([
                            'min' => 6,
                            'minMessage' => 'Password must be at least {{ limit }} characters long',
                            'groups' => [self::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD],
                        ]),
                    ],
                ],
                'invalid_message' => 'Passwords do not match',
            ]);
        }

        if ($options['domain_id'] === Domain::SECOND_DOMAIN_ID) {
            $builder->add('companyTaxNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\Length(['min' => 10, 'max' => 10]),
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím DIČ-2',
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

        if ($this->heurekaFacade->isHeurekaShopCertificationActivated($this->domain->getId())) {
            $builder->add('disallowHeurekaVerifiedByCustomers', CheckboxType::class, [
                'required' => false,
            ]);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'order_personal_info_form';
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['domain_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => FrontOrderData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \App\Model\Order\FrontOrderData $orderData */
                    $orderData = $form->getData();
                    $customerInfo = $this->customerUserFacade->getCustomerInfo($orderData->email, $this->domain->getId());

                    if ($orderData->companyCustomer) {
                        $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
                    } else {
                        $validationGroups[] = self::VALIDATION_GROUP_COMMON_CUSTOMER;
                    }

                    if (!$orderData->deliveryAddressSameAsBillingAddress && $orderData->deliveryAddress === null) {
                        $validationGroups[] = self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS;
                    }

                    if ($orderData->register) {
                        $validationGroups[] = self::VALIDATION_GROUP_REGISTRATION;
                        if ($customerInfo['exists'] === false) {
                            $validationGroups[] = self::VALIDATION_GROUP_REGISTRATION_WITH_PASSWORD;
                        }
                    }

                    return $validationGroups;
                },
            ]);
    }
}
