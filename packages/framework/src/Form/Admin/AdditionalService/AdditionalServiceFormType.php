<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\AdditionalService;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\AdditionalServicePriceAndVatTableByDomainsType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEntityField;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceData;
use Shopsys\FrameworkBundle\Model\AdditionalService\ZboziServiceTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class AdditionalServiceFormType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
        private readonly ZboziServiceTypeEnum $zboziServiceTypeEnum,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService|null $additionalService */
        $additionalService = $options['additionalService'];

        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($additionalService instanceof AdditionalService) {
            $builderBasicInformationGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $additionalService->getId(),
            ]);
        }

        $builderBasicInformationGroup
            ->add('catnum', TextType::class, [
                'label' => 'Catalog number',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter catalog number'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Catalog number cannot be longer than {{ limit }} characters',
                    ),
                    new UniqueEntityField(
                        entityInstance: $additionalService,
                        message: 'Additional service with entered catalog number already exists',
                        fieldName: 'catnum',
                        entityName: AdditionalService::class,
                    ),
                ],
            ])
            ->add('name', LocalizedType::class, [
                'label' => 'Name',
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
            ])
            ->add('enabledByDomainId', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ]);

        $builderPricesGroup = $builder->create('prices', GroupType::class, [
            'label' => 'Prices and VAT',
        ]);

        $builderPricesGroup
            ->add('priceAndVatByDomains', AdditionalServicePriceAndVatTableByDomainsType::class, [
                'inherit_data' => true,
                'label' => false,
            ]);

        $builderFeedsGroup = $builder->create('feeds', GroupType::class, [
            'label' => 'Price comparison feeds',
        ]);

        $builderFeedsGroup
            ->add('showInFeedsByDomainId', DomainsType::class, [
                'required' => false,
                'label' => 'Show in price comparison feeds',
                'help' => t('Applies to Heureka, Google, Zboží.cz and Mergado feeds together.'),
                'row_attr' => [
                    'data-js-additional-service-show-in-feeds' => null,
                ],
            ])
            ->add('feedName', LocalizedType::class, [
                'label' => 'Name for price comparison feeds',
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 128,
                            maxMessage: 'Name for price comparison feeds cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'row_attr' => [
                    'data-js-additional-service-feed-field' => null,
                ],
            ])
            ->add('zboziServiceType', ChoiceType::class, [
                'label' => 'Service type for Zboží.cz',
                'required' => false,
                'placeholder' => '---',
                'choices' => $this->zboziServiceTypeEnum->getAllIndexedByTranslations(),
                'row_attr' => [
                    'data-js-additional-service-feed-field' => null,
                ],
            ])
            ->add('zboziDescription', LocalizedType::class, [
                'label' => 'Additional description for Zboží.cz',
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 160,
                            maxMessage: 'Additional description for Zboží.cz cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'row_attr' => [
                    'data-js-additional-service-feed-field' => null,
                ],
            ]);

        $builderAdditionalInformationGroup = $builder->create('additionalInformation', GroupType::class, [
            'label' => 'Additional information',
        ]);

        $builderAdditionalInformationGroup
            ->add('description', LocalizedType::class, [
                'required' => false,
                'entry_type' => CKEditorType::class,
                'label' => 'Description',
            ])
            ->add('deliveryDaysExtension', IntegerType::class, [
                'required' => false,
                'label' => 'Delivery extension in working days',
                'help' => t('By how many working days the service extends the estimated delivery time of the order.'),
                'empty_data' => '0',
                'attr' => [
                    'placeholder' => '0',
                ],
                'constraints' => [
                    new Constraints\GreaterThanOrEqual(0),
                ],
            ]);

        $builderImageGroup = $builder->create('imageGroup', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => 'Upload image',
                'image_entity_class' => AdditionalService::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $additionalService,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderPricesGroup)
            ->add($builderFeedsGroup)
            ->add($builderAdditionalInformationGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_crud_additional_service_list',
                'entity' => $additionalService,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('additionalService')
            ->setAllowedTypes('additionalService', [AdditionalService::class, 'null'])
            ->setDefaults([
                'data_class' => AdditionalServiceData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Callback(callback: [$this, 'validateNames']),
                    new Callback(callback: [$this, 'validateVats']),
                    new Callback(callback: [$this, 'validateFeedFields']),
                ],
            ]);
    }

    public function validateNames(
        AdditionalServiceData $additionalServiceData,
        ExecutionContextInterface $context,
    ): void {
        foreach ($this->getLocalesOfCheckedDomains($additionalServiceData->enabledByDomainId) as $locale) {
            if (($additionalServiceData->name[$locale] ?? null) === null) {
                $context->buildViolation(
                    t(
                        'Please enter name',
                        [],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('name[' . $locale . ']')
                    ->addViolation();
            }
        }
    }

    public function validateVats(
        AdditionalServiceData $additionalServiceData,
        ExecutionContextInterface $context,
    ): void {
        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $useProductVatRate = $additionalServiceData->useProductVatRateByDomainId[$domainId] ?? true;

            if ($useProductVatRate === true) {
                continue;
            }

            if (($additionalServiceData->vatsIndexedByDomainId[$domainId] ?? null) === null) {
                $context->buildViolation(
                    t(
                        'Please enter VAT rate for domain %domainName%.',
                        ['%domainName%' => $this->domain->getDomainConfigById($domainId)->getName()],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('vatsIndexedByDomainId[' . $domainId . ']')
                    ->addViolation();
            }
        }
    }

    public function validateFeedFields(
        AdditionalServiceData $additionalServiceData,
        ExecutionContextInterface $context,
    ): void {
        $localesShownInFeeds = $this->getLocalesOfCheckedDomains($additionalServiceData->showInFeedsByDomainId);

        if ($localesShownInFeeds === []) {
            return;
        }

        foreach ($localesShownInFeeds as $locale) {
            if (($additionalServiceData->feedName[$locale] ?? null) === null) {
                $context->buildViolation(
                    t(
                        'Please enter name for price comparison feeds.',
                        [],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('feedName[' . $locale . ']')
                    ->addViolation();
            }

            if (($additionalServiceData->zboziDescription[$locale] ?? null) === null) {
                $context->buildViolation(
                    t(
                        'Please enter additional description for Zboží.cz.',
                        [],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                )
                    ->atPath('zboziDescription[' . $locale . ']')
                    ->addViolation();
            }
        }

        if ($additionalServiceData->zboziServiceType === null) {
            $context->buildViolation(
                t(
                    'Please select service type for Zboží.cz.',
                    [],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            )
                ->atPath('zboziServiceType')
                ->addViolation();
        }
    }

    /**
     * @param array<int, bool> $checkedByDomainId
     * @return string[]
     */
    private function getLocalesOfCheckedDomains(array $checkedByDomainId): array
    {
        $locales = [];

        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            if (($checkedByDomainId[$domainId] ?? false) === false) {
                continue;
            }

            $locales[] = $this->domain->getDomainConfigById($domainId)->getLocale();
        }

        return array_values(array_unique($locales));
    }
}
