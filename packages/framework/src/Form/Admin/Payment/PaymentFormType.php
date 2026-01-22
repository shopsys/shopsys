<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Payment;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Form\PriceAndVatTableByDomainsType;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PaymentFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentTypeProvider $paymentTypeProvider
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade $paymentInstructionFacade
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly PaymentFacade $paymentFacade,
        private readonly GoPayPaymentMethodFacade $goPayPaymentMethodFacade,
        private readonly Domain $domain,
        private readonly PaymentTypeProvider $paymentTypeProvider,
        private readonly PaymentInstructionFacade $paymentInstructionFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment */
        $payment = $options['payment'];
        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($payment instanceof Payment) {
            $builderBasicInformationGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $payment->getId(),
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
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ]);

        if ($payment !== null) {
            $this->addHiddenByGoPayWarning(
                $options['data'],
                $builderBasicInformationGroup,
            );
        }

        $builderBasicInformationGroup->add('hidden', YesNoType::class, [
            'label' => 'Hidden',
        ])
            ->add('transports', ChoiceType::class, [
                'required' => false,
                'choices' => $this->transportFacade->getAll(),
                'choice_label' => function (Transport $transport) {
                    return $transport->getName() ?? t('Name has not been entered in your current language') . ' (ID: ' . $transport->getId() . ')';
                },
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'empty_message' => t('You have to create some shipping first.'),
                'label' => 'Available shipping methods',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => $this->paymentTypeProvider->getAllIndexedByTranslations(),
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
                'label' => 'GoPay payment method',
                'required' => true,
                'row_attr' => [
                    'class' => 'js-payment-gopay-payment-method',
                ],
            ])
            ->add('accountNumberByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'label' => 'Account number',
                'required' => true,
                'row_attr' => [
                    'class' => 'js-payment-bank-transfer',
                ],
            ])
            ->add('ibanByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'label' => 'IBAN',
                'required' => true,
                'row_attr' => [
                    'class' => 'js-payment-bank-transfer',
                ],
            ])
            ->add('bicSwiftByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'label' => 'BIC/Swift',
                'required' => true,
                'row_attr' => [
                    'class' => 'js-payment-bank-transfer',
                ],
            ]);

        $builderPriceGroup = $builder->create('prices', GroupType::class, [
            'label' => 'Prices',
        ]);

        $builderPriceGroup
            ->add('czkRounding', YesNoType::class, [
                'label' => 'Order in CZK round to whole crowns',
                'help' => t('Rounding item with 0 % VAT will be added to your order. It is used for payment in cash.'),
            ])
            ->add('pricesByDomains', PriceAndVatTableByDomainsType::class, [
                'pricesIndexedByDomainId' => $this->paymentFacade->getPricesIndexedByDomainId($payment),
                'inherit_data' => true,
                'label' => false,
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
                'entry_options' => [
                    'available_variables' => $this->paymentInstructionFacade->getInstructionsPlaceholders(),
                ],
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);
        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => 'Upload image',
                'image_entity_class' => Payment::class,
                'file_constraints' => [
                    new Constraints\Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $payment,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderPriceGroup)
            ->add($builderAdditionalInformationGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_transportandpayment_list',
                'entity' => $options['payment'],
            ]);
    }

    /**
     * @return array
     */
    private function getGopayPaymentMethodOptionsByDomainId(): array
    {
        $allGoPayPaymentMethods = $this->goPayPaymentMethodFacade->getAll();
        $optionsByDomainId = [];

        $adminEnabledDomainIds = $this->domain->getAdminEnabledDomainIds();

        foreach ($allGoPayPaymentMethods as $goPayPaymentMethod) {
            if (!in_array($goPayPaymentMethod->getDomainId(), $adminEnabledDomainIds, true)) {
                continue;
            }

            $optionsByDomainId[$goPayPaymentMethod->getDomainId()]['choices'][] = $goPayPaymentMethod;
        }

        foreach ($optionsByDomainId as $domainId => $options) {
            if (!in_array($domainId, $adminEnabledDomainIds, true)) {
                continue;
            }

            $optionsByDomainId[$domainId]['group_by'] = function (GoPayPaymentMethod $goPayPaymentMethod): string {
                return $goPayPaymentMethod->isAvailable() ? t('Available') : t('Hidden in GoPay');
            };
        }

        return $optionsByDomainId;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('payment')
            ->setAllowedTypes('payment', [Payment::class, 'null'])
            ->setDefaults([
                'data_class' => PaymentData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Callback(callback: [$this, 'validateGopayPaymentMethod']),
                    new Callback(callback: [$this, 'validateBankTransferType']),
                ],
            ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateBankTransferType(PaymentData $paymentData, ExecutionContextInterface $context): void
    {
        if ($paymentData->type !== PaymentTypeEnum::TYPE_BANK_TRANSFER) {
            return;
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($paymentData->enabled[$domainId] === false) {
                continue;
            }

            $accountNumber = $paymentData->accountNumberByDomainId[$domainId] ?? null;

            if ($accountNumber === null || trim((string)$accountNumber) === '') {
                $context->buildViolation(
                    t(
                        'Please enter account number for domain %domainName%.',
                        ['%domainName%' => $this->domain->getDomainConfigById($domainId)->getName()],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('accountNumberByDomainId[' . $domainId . ']')
                    ->addViolation();
            } else {
                $context->getValidator()
                    ->inContext($context)
                    ->atPath('accountNumberByDomainId[' . $domainId . ']')
                    ->validate(
                        $accountNumber,
                        new Constraints\Length(
                            max: 50,
                            maxMessage: 'Account number cannot be longer than {{ limit }} characters.',
                        ),
                    );
            }

            $iban = $paymentData->ibanByDomainId[$domainId] ?? null;

            if ($iban === null || trim((string)$iban) === '') {
                $context->buildViolation(
                    t(
                        'Please enter IBAN for domain %domainName%.',
                        ['%domainName%' => $this->domain->getDomainConfigById($domainId)->getName()],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('ibanByDomainId[' . $domainId . ']')
                    ->addViolation();
            } else {
                $context->getValidator()
                    ->inContext($context)
                    ->atPath('ibanByDomainId[' . $domainId . ']')
                    ->validate($iban, new Constraints\Iban(message: 'Please enter a valid IBAN.'))
                    ->validate(
                        $iban,
                        new Constraints\Length(
                            max: 50,
                            maxMessage: 'IBAN cannot be longer than {{ limit }} characters',
                        ),
                    );
            }

            $bicSwift = $paymentData->bicSwiftByDomainId[$domainId] ?? null;

            if ($bicSwift === null || trim((string)$bicSwift) === '') {
                $context->buildViolation(
                    t(
                        'Please enter BIC/SWIFT for domain %domainName%.',
                        ['%domainName%' => $this->domain->getDomainConfigById($domainId)->getName()],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('bicSwiftByDomainId[' . $domainId . ']')
                    ->addViolation();
            } else {
                $context->getValidator()
                    ->inContext($context)
                    ->atPath('bicSwiftByDomainId[' . $domainId . ']')
                    ->validate(
                        $bicSwift,
                        new Constraints\Length(
                            max: 50,
                            maxMessage: 'BIC/Swift cannot be longer than {{ limit }} characters.',
                        ),
                    );
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateGopayPaymentMethod(PaymentData $paymentData, ExecutionContextInterface $context): void
    {
        if ($paymentData->type !== PaymentTypeEnum::TYPE_GOPAY) {
            return;
        }

        foreach ($paymentData->enabled as $domainId => $enabled) {
            if (!in_array($domainId, $this->domain->getAdminEnabledDomainIds(), true)) {
                continue;
            }

            if ($enabled && $paymentData->goPayPaymentMethodByDomainId[$domainId] === null) {
                $context->buildViolation(
                    t(
                        'Please select GoPay payment method for enabled domain %domainName%.',
                        ['%domainName%' => $this->domain->getDomainConfigById($domainId)->getName()],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
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

        $builder->add('hiddenByGoPay', MessageType::class, [
            'data' => t('This payment method is hidden by GoPay on domains: %domains%', [
                '%domains%' => implode(', ', $domainNames),
            ]),
        ]);
    }
}
