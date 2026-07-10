<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transport;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\DaysOfWeekType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DisplayVariablesType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Form\TransportInputPricesType;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class TransportFormType extends AbstractType
{
    public function __construct(
        private readonly PaymentFacade $paymentFacade,
        private readonly TransportFacade $transportFacade,
        private readonly TransportGroupFacade $transportGroupFacade,
        private readonly Domain $domain,
        private readonly TransportTypeProvider $transportTypeProvider,
        private readonly AdministrationRouter $administrationRouter,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transport */
        $transport = $options['transport'];
        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($transport instanceof Transport) {
            $builderBasicInformationGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $transport->getId(),
            ]);
        }
        $builderBasicInformationGroup
            ->add('name', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ])
            ->add('hidden', YesNoType::class, [
                'label' => 'Hidden',
            ])
            ->add('payments', ChoiceType::class, [
                'required' => false,
                'choices' => $this->paymentFacade->getAll(),
                'choice_label' => function (Payment $payment) {
                    return $payment->getName() ?? t('Name has not been entered in your current language') . ' (ID: ' . $payment->getId() . ')';
                },
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'empty_message' => t('You have to create some payment first.'),
                'label' => 'Available payment methods',
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => $this->getTypeChoices($transport),
                'disabled' => $transport instanceof Transport && $transport->isEmailType(),
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Transport type',
            ])
            ->add('group', ChoiceType::class, [
                'required' => false,
                'choices' => $this->transportGroupFacade->getAll(),
                'choice_label' => function (TransportGroup $transportGroup) {
                    return $transportGroup->getName() ?? t('Name has not been entered in your current language') . ' (ID: ' . $transportGroup->getId() . ')';
                },
                'choice_value' => 'id',
                'placeholder' => t('-- Choose transport group --'),
                'label' => 'Transport group',
            ]);

        $builderDeliveryGroup = $builder->create('delivery', GroupType::class, [
            'label' => 'Delivery',
        ]);

        $builderDeliveryGroup
            ->add('daysUntilDelivery', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Constraints\GreaterThanOrEqual(value: 0),
                    new Constraints\Regex(pattern: '/^\d+$/'),
                ],
                'label' => 'Days until delivery',
                'help' => t('The number of days between the order dispatch and the delivery. 0 means the goods are delivered on the dispatch day itself.'),
            ])
            ->add('deliveryDaysOfWeek', DaysOfWeekType::class, [
                'label' => 'Days of the week when the transport delivers',
                'constraints' => [
                    new Constraints\Count(min: 1, minMessage: 'Please choose at least one day'),
                ],
            ])
            ->add('deliversOnPublicHolidays', YesNoType::class, [
                'label' => 'Delivers on public holidays as well',
            ])
            ->add('deliversOnInternalClosedDays', YesNoType::class, [
                'label' => 'Delivers on e-shop internal days as well',
            ])
            ->add('deliveryDaysInfo', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('Public holidays and e-shop internal days are managed in the <a href="%closedDaysUrl%" target="_blank">Holidays and internal days</a> administration; the carrier\'s own days off are not taken into account.', [
                    '%closedDaysUrl%' => $this->administrationRouter->generate('admin_closedday_list'),
                ]),
            ]);

        $builderPricesGroup = $builder->create('prices', GroupType::class, [
            'label' => 'Prices',
        ]);

        $optionsByDomainId = [];

        $pricesIndexedByTransportPriceId = $transport instanceof Transport ? $this->transportFacade->getPricesIndexedByTransportPriceId($transport) : [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $optionsByDomainId[$domainId] = [
                'domain_id' => $domainId,
                'current_transport_prices_indexed_by_id' => $pricesIndexedByTransportPriceId,
            ];
        }

        $builderPricesGroup->add('inputPricesByDomain', MultidomainType::class, [
            'label' => false,
            'entry_type' => TransportInputPricesType::class,
            'options_by_domain_id' => $optionsByDomainId,
            'entry_options' => [
                'required' => true,
            ],
            'required' => true,
        ]);

        $builderAdditionalInformationGroup = $builder->create('additionalInformation', GroupType::class, [
            'label' => 'Additional information',
        ]);

        $builderAdditionalInformationGroup
            ->add('description', LocalizedType::class, [
                'required' => false,
                'entry_type' => TextareaType::class,
                'label' => 'Description',
            ])
            ->add('instructions', LocalizedType::class, [
                'required' => false,
                'entry_type' => CKEditorType::class,
                'label' => 'Instructions',
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => 'Upload image',
                'image_entity_class' => Transport::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $transport,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builderPackageTrackingGroup = $builder->create('packageTracking', GroupType::class, [
            'label' => 'Package tracking',
        ]);

        $builderPackageTrackingGroup
            ->add('trackingUrl', TextType::class, [
                'label' => 'Tracking URL',
                'required' => false,
                'constraints' => [
                    new Length(max: 255),
                ],
            ])
            ->add('trackingUrlVariables', DisplayVariablesType::class, [
                'label' => 'Tracking URL variables',
                'required' => false,
                'variables' => [
                    OrderMail::VARIABLE_TRANSPORT_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                ],
            ])
            ->add('trackingInstructions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'label' => 'Tracking instructions',
                'required' => false,
            ])
            ->add('trackingInstructionsVariables', DisplayVariablesType::class, [
                'label' => 'Tracking instructions variables',
                'required' => false,
                'variables' => [
                    OrderMail::VARIABLE_TRANSPORT_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                    OrderMail::VARIABLE_TRANSPORT_TRACKING_URL => [
                        'text' => t('Tracking URL'),
                        'required' => false,
                    ],
                ],
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderDeliveryGroup)
            ->add($builderPricesGroup)
            ->add($builderAdditionalInformationGroup)
            ->add($builderImageGroup)
            ->add($builderPackageTrackingGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_transportandpayment_list',
                'entity' => $options['transport'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('transport')
            ->setAllowedTypes('transport', [Transport::class, 'null'])
            ->setDefaults([
                'data_class' => TransportData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback(callback: [$this, 'validateTransportPricesOnDomain']),
                ],
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function getTypeChoices(?Transport $transport): array
    {
        $typeChoices = $this->transportTypeProvider->getAllIndexedByTranslations();

        if ($transport instanceof Transport && $transport->isEmailType()) {
            return array_filter(
                $typeChoices,
                static fn (string $type): bool => $type === TransportTypeEnum::TYPE_EMAIL,
            );
        }

        return array_filter(
            $typeChoices,
            static fn (string $type): bool => $type !== TransportTypeEnum::TYPE_EMAIL,
        );
    }

    public function validateTransportPricesOnDomain(
        TransportData $transportData,
        ExecutionContextInterface $context,
    ): void {
        foreach ($transportData->inputPricesByDomain as $domainId => $pricesData) {
            $weightLimits = [];

            if ($pricesData->pricesWithLimits === []) {
                $context
                    ->buildViolation(t('Please enter at least one price', [], Translator::VALIDATOR_TRANSLATION_DOMAIN))
                    ->atPath(sprintf('inputPricesByDomain[%d].pricesWithLimits', $domainId))
                    ->addViolation();
            }

            foreach ($pricesData->pricesWithLimits as $priceData) {
                if ($priceData === null) {
                    continue;
                }

                if (in_array($priceData->maxWeight, $weightLimits, true)) {
                    $context
                        ->buildViolation(t('Please use each limit only once', [], Translator::VALIDATOR_TRANSLATION_DOMAIN))
                        ->atPath(sprintf('inputPricesByDomain[%d].pricesWithLimits', $domainId))
                        ->addViolation();
                }
                $weightLimits[] = $priceData->maxWeight;
            }
        }
    }
}
