<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\PhoneType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Length;

final class OrderFormType extends AbstractType
{
    public const string VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS = 'deliveryAddressSameAsBillingAddress';

    public function __construct(
        private readonly CountryFacade $countryFacade,
        private readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];
        $domainId = $order->getDomainId();
        $countries = $this->countryFacade->getAllOnDomain($domainId);

        $builder
            ->add($this->createBasicInformationGroup($builder, $order))
            ->add($this->createPersonalDataGroup($builder, $domainId))
            ->add($this->createCompanyDataGroup($builder))
            ->add($this->createBillingDataGroup($builder, $countries))
            ->add($this->createShippingAddressGroup($builder, $countries, $domainId))
            ->add($this->createNoteGroup($builder));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'data_class' => OrderData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData */
                    $orderData = $form->getData();

                    if (!$orderData->deliveryAddressSameAsBillingAddress) {
                        $validationGroups[] = self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS;
                    }

                    /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
                    $order = $form->getConfig()->getOption('order');
                    $withdrawalRequest = $this->withdrawalRequestFacade->findConfirmedByOrder($order);

                    if (
                        $withdrawalRequest !== null ||
                        $orderData->status?->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN
                    ) {
                        $validationGroups[] = OrderWithdrawalFormType::VALIDATION_GROUP_WITHDRAWAL_REQUIRED;
                    }

                    return $validationGroups;
                },
            ]);
    }

    private function createBasicInformationGroup(FormBuilderInterface $builder, Order $order): FormBuilderInterface
    {
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        $builderBasicInformationGroup->add($this->createWithdrawalRequestGroup($builderBasicInformationGroup, $order->getDomainId()));

        $builderBasicInformationGroup
            ->add('trackingNumber', TextType::class, [
                'label' => 'Tracking number',
                'required' => false,
                'constraints' => [
                    new Length(max: 100),
                ],
            ])
            ->add('deliveredAt', DateTimeType::class, [
                'label' => 'Delivered at',
                'required' => false,
            ]);

        return $builderBasicInformationGroup;
    }

    private function createPersonalDataGroup(FormBuilderInterface $builder, int $domainId): FormBuilderInterface
    {
        $builderPersonalDataGroup = $builder->create('personalDataGroup', GroupType::class, [
            'label' => 'Personal data',
        ]);

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'label' => 'First name',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter first name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'First name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter last name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Last name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter email'),
                    new Email(message: 'Please enter valid email'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('telephone', PhoneType::class, [
                'label' => 'Telephone',
                'domain_id' => $domainId,
                'required' => true,
            ]);

        return $builderPersonalDataGroup;
    }

    private function createCompanyDataGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderCompanyDataGroup = $builder->create('companyDataGroup', GroupType::class, [
            'label' => 'Company data',
        ]);

        $builderCompanyDataGroup
            ->add('companyName', TextType::class, [
                'label' => 'Company name',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Company name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('companyNumber', TextType::class, [
                'label' => 'Company number',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 50,
                        maxMessage: 'Identification number cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('companyTaxNumber', TextType::class, [
                'label' => 'Tax number',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 50,
                        maxMessage: 'Tax number cannot be longer than {{ limit }} characters',
                    ),
                ],
            ]);

        return $builderCompanyDataGroup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country[] $countries
     */
    private function createBillingDataGroup(FormBuilderInterface $builder, array $countries): FormBuilderInterface
    {
        $builderBillingDataGroup = $builder->create('billingDataGroup', GroupType::class, [
            'label' => 'Billing data',
        ]);

        $builderBillingDataGroup
            ->add('street', TextType::class, [
                'label' => 'Street',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter street'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter city'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'City name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Postcode',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter zip code'),
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose country'),
                ],
            ]);

        return $builderBillingDataGroup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country[] $countries
     */
    private function createShippingAddressGroup(
        FormBuilderInterface $builder,
        array $countries,
        int $domainId,
    ): FormBuilderInterface {
        $builderShippingAddressGroup = $builder->create('shippingAddressGroup', GroupType::class, [
            'label' => 'Delivery address',
        ]);

        $builderShippingAddressGroup
            ->add('deliveryAddressSameAsBillingAddress', CheckboxType::class, [
                'label' => 'Delivery address is the same as billing address',
                'required' => false,
            ])
            ->add(
                $builderShippingAddressGroup
                    ->create('deliveryAddressFields', FormType::class, [
                        'inherit_data' => true,
                        'label' => false,
                    ])
                    ->add('deliveryFirstName', TextType::class, [
                        'label' => 'First name',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter first name of contact person',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'First name of contact person cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryLastName', TextType::class, [
                        'label' => 'Last name',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter last name of contact person',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'Last name of contact person cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryCompanyName', TextType::class, [
                        'label' => 'Company',
                        'required' => false,
                        'constraints' => [
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'Name cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryTelephone', PhoneType::class, [
                        'label' => 'Telephone',
                        'domain_id' => $domainId,
                        'required' => false,
                        'constraint_groups' => [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                    ])
                    ->add('deliveryStreet', TextType::class, [
                        'label' => 'Street',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter street',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryCity', TextType::class, [
                        'label' => 'City',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter city',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'City name cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryPostcode', TextType::class, [
                        'label' => 'Postcode',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter zip code',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 30,
                                maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                                groups: [self::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryCountry', ChoiceType::class, [
                        'label' => 'Country',
                        'required' => true,
                        'choices' => $countries,
                        'choice_label' => 'name',
                        'choice_value' => 'id',
                        'constraints' => [
                            new Constraints\NotBlank(message: 'Please choose country'),
                        ],
                    ]),
            );

        return $builderShippingAddressGroup;
    }

    private function createNoteGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderNoteGroup = $builder->create('noteGroup', GroupType::class, [
            'label' => 'Note',
        ]);

        $builderNoteGroup
            ->add('note', TextareaType::class, [
                'label' => 'Contact us',
                'required' => false,
            ]);

        return $builderNoteGroup;
    }

    private function createWithdrawalRequestGroup(
        FormBuilderInterface $builder,
        int $domainId,
    ): FormBuilderInterface {
        $builderWithdrawalRequestGroup = $builder->create('withdrawalRequestGroup', GroupType::class, [
            'label' => 'Withdrawal Request',
        ]);

        $builderWithdrawalRequestGroup
            ->add('withdrawalRequestData', OrderWithdrawalFormType::class, [
                'label' => false,
                'domain_id' => $domainId,
            ]);

        return $builderWithdrawalRequestGroup;
    }
}
