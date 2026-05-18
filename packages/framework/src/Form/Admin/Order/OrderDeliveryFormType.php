<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\PhoneType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderDeliveryFormType extends AbstractType
{
    public const string VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS = 'deliveryAddressSameAsBillingAddress';

    public function __construct(
        private readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];
        $countries = $this->countryFacade->getAllOnDomain($order->getDomainId());

        $builder
            ->add('deliveryAddressSameAsBillingAddress', CheckboxType::class, [
                'label' => 'Delivery address is the same as billing address',
                'required' => false,
                'attr' => [
                    'data-checkbox-toggle-container-class' => 'js-delivery-address-fields',
                    'class' => 'js-checkbox-toggle js-checkbox-toggle--inverted js-checkbox-toggle--disable-container',
                ],
            ])
            ->add(
                $builder
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
                    ->add('deliveryTelephone', PhoneType::class, [
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
                            new Constraints\NotBlank(
                                message: 'Please choose country',
                                groups: [static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS],
                            ),
                        ],
                    ]),
            );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('order')
            ->setAllowedTypes('order', Order::class)
            ->setDefaults([
                'inherit_data' => true,
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData */
                    $orderData = $form->getData();

                    if (!$orderData->deliveryAddressSameAsBillingAddress) {
                        $validationGroups[] = static::VALIDATION_GROUP_DELIVERY_ADDRESS_SAME_AS_BILLING_ADDRESS;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
