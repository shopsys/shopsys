<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Payment;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\PriceAndVatTableByDomainsType;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaymentFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $payment */
        $payment = $options['payment'];
        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => t('Basic information'),
        ]);

        if ($payment instanceof Payment) {
            $builderBasicInformationGroup->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $payment->getId(),
            ]);
        }

        $builderBasicInformationGroup
            ->add('name', LocalizedType::class, [
                'main_constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                    ],
                ],
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hidden'),
            ])
            ->add('transports', ChoiceType::class, [
                'required' => false,
                'choices' => $this->transportFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'empty_message' => t('You have to create some shipping first.'),
                'label' => t('Available shipping methods'),
            ]);

        $builderPriceGroup = $builder->create('prices', GroupType::class, [
            'label' => t('Prices'),
        ]);

        $builderPriceGroup
            ->add('czkRounding', YesNoType::class, [
                'required' => false,
                'label' => t('Order in CZK round to whole crowns'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Rounding item with 0 % VAT will be added to your order. It is used for payment in cash.'),
                ],
            ])
            ->add('pricesByDomains', PriceAndVatTableByDomainsType::class, [
                'pricesIndexedByDomainId' => $this->paymentFacade->getPricesIndexedByDomainId($payment),
                'inherit_data' => true,
                'render_form_row' => false,
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
                'image_entity_class' => Payment::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $payment,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderPriceGroup)
            ->add($builderAdditionalInformationGroup)
            ->add($builderImageGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('payment')
            ->setAllowedTypes('payment', [Payment::class, 'null'])
            ->setDefaults([
                'data_class' => PaymentData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
