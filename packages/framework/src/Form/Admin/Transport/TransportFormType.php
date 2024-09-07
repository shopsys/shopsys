<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transport;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DisplayVariablesType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\TransportInputPricesType;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransportFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum $transportTypeEnum
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly PaymentFacade $paymentFacade,
        private readonly TransportFacade $transportFacade,
        private readonly TransportTypeEnum $transportTypeEnum,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transport */
        $transport = $options['transport'];
        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => t('Basic information'),
        ]);

        if ($transport instanceof Transport) {
            $builderBasicInformationGroup->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $transport->getId(),
            ]);
        }
        $builderBasicInformationGroup
            ->add('name', LocalizedType::class, [
                'main_constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(
                            ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
                'label' => t('Name'),
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hidden'),
            ])
            ->add('payments', ChoiceType::class, [
                'required' => false,
                'choices' => $this->paymentFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'empty_message' => t('You have to create some payment first.'),
                'label' => t('Available payment methods'),
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => $this->transportTypeEnum->getAllIndexedByTranslations(),
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => t('Transport type'),
            ])
            ->add('daysUntilDelivery', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Constraints\GreaterThanOrEqual([
                        'value' => 0,
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^\d+$/',
                    ]),
                ],
                'label' => t('Days until delivery'),
            ])
            ->add('maxWeight', IntegerType::class, [
                'label' => t('Maximum weight (g)'),
                'required' => false,
            ]);

        $builderPricesGroup = $builder->create('prices', GroupType::class, [
            'label' => t('Prices'),
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
        ]);

        $builderAdditionalInformationGroup = $builder->create('additionalInformation', GroupType::class, [
            'label' => t('Additional information'),
        ]);

        $builderAdditionalInformationGroup
            ->add('description', LocalizedType::class, [
                'required' => false,
                'entry_type' => TextareaType::class,
                'label' => t('Description'),
            ])
            ->add('instructions', LocalizedType::class, [
                'required' => false,
                'entry_type' => CKEditorType::class,
                'label' => t('Instructions'),
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => t('Image'),
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => t('Upload image'),
                'image_entity_class' => Transport::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $transport,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builderPackageTrackingGroup = $builder->create('packageTracking', GroupType::class, [
            'label' => t('Package tracking'),
        ]);

        $builderPackageTrackingGroup
            ->add('trackingUrl', TextType::class, [
                'label' => t('Tracking URL'),
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('trackingUrlVariables', DisplayVariablesType::class, [
                'label' => t('Tracking URL variables'),
                'required' => false,
                'variables' => [
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                ],
            ])
            ->add('trackingInstructions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'label' => t('Tracking instructions'),
                'required' => false,
                'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
            ])
            ->add('trackingInstructionsVariables', DisplayVariablesType::class, [
                'label' => t('Tracking instructions variables'),
                'required' => false,
                'variables' => [
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_NUMBER => [
                        'text' => t('Tracking number'),
                        'required' => false,
                    ],
                    OrderMail::TRANSPORT_VARIABLE_TRACKING_URL => [
                        'text' => t('Tracking URL'),
                        'required' => false,
                    ],
                ],
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderPricesGroup)
            ->add($builderAdditionalInformationGroup)
            ->add($builderImageGroup)
            ->add($builderPackageTrackingGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('transport')
            ->setAllowedTypes('transport', [Transport::class, 'null'])
            ->setDefaults([
                'data_class' => TransportData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
