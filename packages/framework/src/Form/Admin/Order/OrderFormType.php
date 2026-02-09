<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionsType;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyCompanyNameType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyCustomerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyUrlType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Form\OrderItemsType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayOrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
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
        private readonly OrderStatusFacade $orderStatusFacade,
        private readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        private readonly Domain $domain,
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
            ->add($this->createPersonalDataGroup($builder))
            ->add($this->createCompanyDataGroup($builder))
            ->add($this->createBillingDataGroup($builder, $countries))
            ->add($this->createShippingAddressGroup($builder, $countries))
            ->add($this->createNoteGroup($builder))
            ->add('orderItems', OrderItemsType::class, [
                'order' => $order,
            ]);

        if ($order->getPaymentTransactionsCount() > 0) {
            $builder->add($this->createPaymentTransactionsGroup($builder, $order));
        }

        $builder
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_order_list',
                'entity' => $options['order'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'data_class' => OrderData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData */
                    $orderData = $form->getData();

                    if (!$orderData->deliveryAddressSameAsBillingAddress) {
                        $validationGroups[] = static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS;
                    }

                    /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
                    $order = $form->getConfig()->getOption('order');
                    $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

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
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);
        $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

        if ($withdrawalRequest !== null) {
            $builderBasicInformationGroup
                ->add('withdrawalWarning', MessageType::class, [
                    'message_level' => MessageType::MESSAGE_LEVEL_WARNING,
                    'data' => t('There is a withdrawal request for this order.'),
                ]);
        }

        $builderBasicInformationGroup
            ->add('id', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $order->getId(),
            ])
            ->add('orderDetail', DisplayOnlyUrlType::class, [
                'label' => 'Order detail',
                'route' => 'front_customer_order_detail_unregistered',
                'route_params' => [
                    'urlHash' => $order->getUrlHash(),
                ],
                'domain_id' => $order->getDomainId(),
            ]);

        if ($this->domain->isMultidomain()) {
            $builderBasicInformationGroup
                ->add('domainIcon', DisplayOnlyDomainIconType::class, [
                    'label' => 'Domain',
                    'data' => $order->getDomainId(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('orderNumber', DisplayOnlyType::class, [
                'label' => 'Order number',
                'data' => $order->getNumber(),
            ])
            ->add('dateOfCreation', DisplayOnlyType::class, [
                'label' => 'Date of creation and privacy policy agreement',
                'data' => $this->dateTimeFormatterExtension->formatDateTime($order->getCreatedAt()),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => $this->orderStatusFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_attr' => function (OrderStatus $orderStatus) {
                    return [
                        'data-js-order-status-type' => $orderStatus->getType(),
                    ];
                },
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'data-js-order-status-select' => null,
                ],
            ]);

        $builderBasicInformationGroup->add($this->createWithdrawalRequestGroup($builderBasicInformationGroup, $withdrawalRequest));

        if ($order->getCreatedAsAdministrator() || $order->getCreatedAsAdministratorName()) {
            $builderBasicInformationGroup
                ->add('createdAsAdministrator', DisplayOnlyType::class, [
                    'label' => 'Created by administrator',
                    'data' => $order->getCreatedAsAdministrator() === null ? $order->getCreatedAsAdministratorName() : $order->getCreatedAsAdministrator()->getRealName(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('user', DisplayOnlyCustomerType::class, [
                'label' => 'Customer',
                'user' => $order->getCustomerUser(),
            ]);

        if ($domainConfig->isB2b() && $order->getCustomer()?->getBillingAddress()->getCompanyNumber() !== null) {
            $builderBasicInformationGroup
                ->add('company', DisplayOnlyCompanyNameType::class, [
                    'label' => 'Company',
                    'customer' => $order->getCustomer(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('heurekaAgreement', DisplayOnlyType::class, [
                'label' => 'Heureka agreement',
                'data' => $order->isHeurekaAgreement() ? t('Yes') : t('No'),
            ]);

        if ($order->getOrigin() !== null) {
            $builderBasicInformationGroup
                ->add('origin', DisplayOnlyType::class, [
                    'label' => 'Origin',
                    'data' => $order->getOrigin(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('payment', DisplayOnlyType::class, [
                'label' => 'Payment type',
                'data' => $order->getPayment()->getName(),
            ]);

        if ($order->getPayment()->isGoPay() === true) {
            $goPayPaymentTransaction = $order->getLastGoPayTransaction();

            if ($goPayPaymentTransaction !== null) {
                $translatedGoPayStatus = GoPayOrderStatus::getTranslatedGoPayStatus($goPayPaymentTransaction->getExternalPaymentStatus());
                $translatedGoPaySubStatus = GoPayOrderStatus::getTranslatedGoPaySubStatus($goPayPaymentTransaction->getExternalPaymentSubStatus());

                if ($translatedGoPaySubStatus !== null) {
                    $translatedGoPayStatus .= ' - ' . $translatedGoPaySubStatus;
                }
            } else {
                $translatedGoPayStatus = t('Order has not been sent to GoPay');
            }

            $builderBasicInformationGroup
                ->add('gopayStatus', DisplayOnlyType::class, [
                    'label' => 'GoPay payment status',
                    'data' => $translatedGoPayStatus,
                ]);
        }

        $builderBasicInformationGroup
            ->add('transport', DisplayOnlyType::class, [
                'label' => 'Transport type',
                'data' => $order->getTransport()->getName(),
            ])
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

        $promoCode = $order->getPromoCode();

        if ($promoCode !== null) {
            $builderBasicInformationGroup
                ->add('promoCode', DisplayOnlyType::class, [
                    'label' => 'Promo code',
                    'data' => $promoCode,
                ]);
        }

        return $builderBasicInformationGroup;
    }

    private function createPersonalDataGroup(FormBuilderInterface $builder): FormBuilderInterface
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
            ->add('telephone', TextType::class, [
                'label' => 'Telephone',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter telephone number'),
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Telephone number cannot be longer than {{ limit }} characters',
                    ),
                ],
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
    private function createShippingAddressGroup(FormBuilderInterface $builder, array $countries): FormBuilderInterface
    {
        $builderShippingAddressGroup = $builder->create('shippingAddressGroup', GroupType::class, [
            'label' => 'Delivery address',
        ]);

        $builderShippingAddressGroup
            ->add('deliveryAddressSameAsBillingAddress', CheckboxType::class, [
                'label' => 'Delivery address is the same as billing address',
                'required' => false,
                'attr' => [
                    'data-checkbox-toggle-container-class' => 'js-delivery-address-fields',
                    'class' => 'js-checkbox-toggle js-checkbox-toggle--inverted',
                ],
            ])
            ->add(
                $builderShippingAddressGroup
                    ->create('deliveryAddressFields', FormType::class, [
                        'inherit_data' => true,
                        'label' => false,
                        'attr' => [
                            'class' => 'js-delivery-address-fields',
                        ],
                    ])
                    ->add('deliveryFirstName', TextType::class, [
                        'label' => 'First name',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter first name of contact person',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'First name of contact person cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryLastName', TextType::class, [
                        'label' => 'Last name',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter last name of contact person',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'Last name of contact person cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
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
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryTelephone', TextType::class, [
                        'label' => 'Telephone',
                        'required' => false,
                        'constraints' => [
                            new Constraints\Length(
                                max: 30,
                                maxMessage: 'Telephone number cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryStreet', TextType::class, [
                        'label' => 'Street',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter street',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryCity', TextType::class, [
                        'label' => 'City',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter city',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 100,
                                maxMessage: 'City name cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ])
                    ->add('deliveryPostcode', TextType::class, [
                        'label' => 'Postcode',
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank(
                                message: 'Please enter zip code',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                            new Constraints\Length(
                                max: 30,
                                maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
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

    private function createPaymentTransactionsGroup(FormBuilderInterface $builder, Order $order): FormBuilderInterface
    {
        $builderPaymentGroup = $builder->create('paymentTransactionsGroup', GroupType::class, [
            'label' => 'Payment transactions',
        ]);

        $builderPaymentGroup->add('paymentTransactionRefunds', PaymentTransactionsType::class, [
            'entry_type' => PaymentTransactionType::class,
            'error_bubbling' => false,
            'allow_add' => false,
            'allow_delete' => false,
            'required' => false,
            'order' => $order,
        ]);

        return $builderPaymentGroup;
    }

    private function createWithdrawalRequestGroup(
        FormBuilderInterface $builder,
        ?WithdrawalRequest $withdrawalRequest,
    ): FormBuilderInterface {
        $rowAttr = [
            'data-withdrawal-request-exists' => $withdrawalRequest !== null ? 'true' : 'false',
        ];

        if ($withdrawalRequest === null) {
            $rowAttr['style'] = 'display: none;';
        }

        $builderWithdrawalRequestGroup = $builder->create('withdrawalRequestGroup', GroupType::class, [
            'label' => 'Withdrawal Request',
            'row_attr' => $rowAttr,
        ]);

        $builderWithdrawalRequestGroup
            ->add('withdrawalRequestData', OrderWithdrawalFormType::class, [
                'label' => false,
            ]);

        return $builderWithdrawalRequestGroup;
    }
}
