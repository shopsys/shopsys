<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Payment;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\PriceAndVatTableByDomainsType;
use Shopsys\FrameworkBundle\Form\WarningMessageType;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PaymentFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly PaymentFacade $paymentFacade,
        private readonly GoPayPaymentMethodFacade $goPayPaymentMethodFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment */
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
                        new Constraints\Length(
                            ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                        ),
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
            ])
            ->add('type', ChoiceType::class, [
                'label' => t('Type'),
                'choices' => [
                    t('Basic') => Payment::TYPE_BASIC,
                    t('GoPay') => Payment::TYPE_GOPAY,
                ],
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'attr' => [
                    'class' => 'js-payment-type',
                ],
            ])
            ->add('goPayPaymentMethodByDomainId', MultidomainType::class, [
                'entry_type' => ChoiceType::class,
                'options_by_domain_id' => $this->getGopayPaymentMethodOptionsByDomainId(),
                'entry_options' => [
                    'placeholder' => '---',
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                ],
                'label' => t('GoPay payment method'),
                'required' => true,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => null,
                ],
                'attr' => [
                    'class' => 'js-payment-gopay-payment-method',
                ],
            ]);

        if ($payment !== null) {
            $this->addHiddenByGoPayWarning(
                $options['data'],
                $builderBasicInformationGroup,
            );
        }

        $builderPriceGroup = $builder->create('prices', GroupType::class, [
            'label' => t('Prices'),
        ]);

        $builderPriceGroup
            ->add('czkRounding', YesNoType::class, [
                'required' => false,
                'label' => t('Order in CZK round to whole crowns'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t(
                        'Rounding item with 0 % VAT will be added to your order. It is used for payment in cash.',
                    ),
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
     * @return array
     */
    private function getGopayPaymentMethodOptionsByDomainId(): array
    {
        $allGoPayPaymentMethods = $this->goPayPaymentMethodFacade->getAll();
        $optionsByDomainId = [];

        foreach ($allGoPayPaymentMethods as $goPayPaymentMethod) {
            $optionsByDomainId[$goPayPaymentMethod->getDomainId()]['choices'][] = $goPayPaymentMethod;
        }

        return $optionsByDomainId;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('payment')
            ->setAllowedTypes('payment', [Payment::class, 'null'])
            ->setDefaults([
                'data_class' => PaymentData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Callback([$this, 'validateGopayPaymentMethod']),
                ],
            ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateGopayPaymentMethod(PaymentData $paymentData, ExecutionContextInterface $context): void
    {
        if ($paymentData->type !== Payment::TYPE_GOPAY) {
            return;
        }

        foreach ($paymentData->enabled as $domainId => $enabled) {
            if ($enabled && $paymentData->goPayPaymentMethodByDomainId[$domainId] === null) {
                $context->buildViolation('Please select GoPay payment method for enabled domain ' . $this->domain->getDomainConfigById($domainId)->getName())
                    ->atPath('goPayPaymentMethodByDomainId[1]')
                    ->addViolation();
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    public function addHiddenByGoPayWarning(PaymentData $paymentData, FormBuilderInterface $builder): void
    {
        $domainIdsWithHiddenByGoPay = array_keys(array_filter($paymentData->hiddenByGoPay));
        $domainNames = [];

        if (count($domainIdsWithHiddenByGoPay) === 0) {
            return;
        }

        foreach ($domainIdsWithHiddenByGoPay as $domainId) {
            $domainNames[] = $this->domain->getDomainConfigById($domainId)->getName();
        }

        $builder->add('hiddenByGoPay', WarningMessageType::class, [
            'data' => t('This payment method is hidden by GoPay on domains: %domains%', [
                '%domains%' => implode(', ', $domainNames),
            ]),
            'position' => ['after' => 'enabled'],
        ]);
    }
}
