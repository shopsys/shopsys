<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\DateTimeHelper\DateTimeHelper;
use App\Form\Constraints\UniqueFlags;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Product\Brand\BrandFacade;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
    public const VALIDATION_GROUP_TYPE_PERCENT = 'type_percent';
    public const VALIDATION_GROUP_TYPE_NOMINAL = 'type_nominal';

    private ?PromoCode $promoCode;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly PromoCodeFacade $promoCodeFacade,
        private readonly BrandFacade $brandFacade,
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->promoCode = $options['promo_code'];

        if ($options['mass_generate'] === true) {
            $builder->add($this->addMassGenerationGroup($builder));
            $builder->add('saveAndDownloadCsv', SubmitType::class, [
                'label' => t('Create and download CSV'),
            ]);
        }

        $builder->add('domainId', HiddenType::class, [
            'data' => $this->getDomainId(),
        ]);

        $builder->add('shownDomainId', DomainType::class, [
            'mapped' => false,
            'label' => t('Domain'),
            'disabled' => true,
        ]);

        $this->buildBaseFormGroup($builder, $options);
        $this->buildLimitsFormGroup($builder);
        $this->buildTimeValidationFormGroup($builder);
        $this->buildFlagsFormGroup($builder);
        $this->buildCustomersFormGroup($builder);
        $this->buildProductsWithSaleForm($builder);
        $this->buildCategoriesWithSaleFormGroup($builder);
        $this->buildBrandsWithSaleFormGroup($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    private function buildBaseFormGroup(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('identifier', TextType::class, [
            'label' => t('Promo code identifier in IS'),
            'required' => true,
            'constraints' => [
                new Constraints\NotNull([
                    'message' => 'The identifier must contain two characters',
                ]),
                new Constraints\Length([
                    'min' => 2,
                    'max' => 2,
                    'exactMessage' => 'The identifier must contain two characters',
                ]),
            ],
        ]);

        $builder->add('discountType', ChoiceType::class, [
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                t('Percents') => PromoCode::DISCOUNT_TYPE_PERCENT,
                t('Nominal') => PromoCode::DISCOUNT_TYPE_NOMINAL,
            ],
            'label' => t('Discount type'),
        ]);

        $builder->add('remainingUses', IntegerType::class, [
            'label' => t('Remaining number of uses'),
            'required' => false,
        ]);

        $codeOptions = $builder->get('code')->getOptions();
        $codeOptions['constraints'] = [
            new Constraints\NotBlank(['message' => 'Please enter promo code']),
        ];
        $codeOptions['position'] = 'first';
        $codeOptions['label'] = t('Promo code');
        $builder->add('code', TextType::class, $codeOptions);

        if ($options['mass_generate'] === true) {
            $builder->remove('code');
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildLimitsFormGroup(FormBuilderInterface $builder): void
    {
        $discountOptions = $builder->get('percent')->getOptions();
        $builder->remove('percent');

        $limitsGroup = $builder->create('limitsGroup', GroupType::class, [
            'label' => t('Apply according to the total price of the order'),
        ]);

        $limitsGroup->add(
            $limitsGroup->create('limits', PromoCodeLimitCollectionType::class, [
                'label' => t('Limits'),
                'entry_type' => PromoCodeLimitType::class,
                'entry_options' => ['discount' => $discountOptions],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'constraints' => [
                    new Constraints\Count([
                        'min' => 1,
                        'minMessage' => 'Please enter at least one discount limit',
                    ]),
                ],
            ]),
        );

        $builder->add($limitsGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildTimeValidationFormGroup(FormBuilderInterface $builder): void
    {
        $timeValidationGroup = $builder->create('timeValidationGroup', GroupType::class, [
            'label' => t('Apply according to date and time limit'),
        ]);

        $timeValidationGroup->add('dateValidFrom', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Valid from (date)'),
        ])->add('timeValidFrom', TextType::class, [
            'icon_title' => t('Time format: "hh:mm", e.g.: "07:45", "23:05"'),
            'constraints' => [
                new Constraints\Callback([$this, 'validateTimeIfIsSet']),
            ],
            'required' => false,
            'label' => t('Valid from (time)'),
        ])->add('dateValidTo', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Valid to (date)'),
        ])->add('timeValidTo', TextType::class, [
            'icon_title' => t('Time format: "hh:mm", e.g.: "07:45", "23:05"'),
            'constraints' => [
                new Constraints\Callback([$this, 'validateTimeIfIsSet']),
            ],
            'required' => false,
            'label' => t('Valid to (time)'),
        ]);

        $builder->add($timeValidationGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildFlagsFormGroup(FormBuilderInterface $builder): void
    {
        $flagsGroup = $builder->create('flagsGroup', GroupType::class, [
            'label' => t('Apply according to product flags'),
        ]);

        $flagsGroup->add('flags', PromoCodeFlagCollectionType::class, [
            'label' => t('Flags'),
            'entry_type' => PromoCodeFlagType::class,
            'entry_options' => ['label' => false],
            'required' => false,
            'allow_add' => true,
            'error_bubbling' => false,
            'allow_delete' => true,
            'constraints' => [
                new UniqueFlags(),
            ],
        ]);

        $builder->add($flagsGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildCustomersFormGroup(FormBuilderInterface $builder): void
    {
        $customersGroup = $builder->create('customersGroup', GroupType::class, [
            'label' => t('Apply according to customer'),
        ]);
        $builder->add($customersGroup);
        $customersGroup->add('registeredCustomerUserOnly', YesNoType::class, [
            'required' => false,
            'label' => t('For registered customers only'),
        ])
            ->add('limitedPricingGroups', ChoiceType::class, [
                'required' => false,
                'choices' => $this->pricingGroupFacade->getByDomainId($this->adminDomainTabsFacade->getSelectedDomainId()),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => t('Pricing groups'),
                'multiple' => true,
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildProductsWithSaleForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('productsWithSale', ProductsType::class, [
                'required' => false,
                'sortable' => true,
                'label' => t('Apply to selected products'),
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildCategoriesWithSaleFormGroup(FormBuilderInterface $builder): void
    {
        $displayCategoriesGroup = $builder->create('displayCategoriesGroup', GroupType::class, [
            'label' => t('Apply to selected categories'),
        ]);
        $displayCategoriesGroup->add('categoriesWithSale', CategoriesType::class, [
            'required' => false,
            'domain_id' => $this->getDomainId(),
            'label' => t('Categories'),
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
        ]);
        $builder->add($displayCategoriesGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildBrandsWithSaleFormGroup(FormBuilderInterface $builder): void
    {
        $displayCategoriesGroup = $builder->create('displayBrandsGroup', GroupType::class, [
            'label' => t('Apply to selected brands'),
        ]);
        $displayCategoriesGroup->add('brandsWithSale', ChoiceType::class, [
            'required' => false,
            'choices' => $this->brandFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'label' => t('Brands'),
            'multiple' => true,
        ]);
        $builder->add($displayCategoriesGroup);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['promo_code', 'mass_generate'])
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setAllowedTypes('mass_generate', 'bool')
            ->setDefaults([
                'mass_generate' => false,
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUniquePromoCodeByDomain']),
                    new Constraints\Callback([$this, 'validateDateTimeFrom']),
                    new Constraints\Callback([$this, 'validateDateTimeTo']),
                ],
                'validation_groups' => static function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
                    /** @var \App\Model\Order\PromoCode\PromoCodeData $promoCodeData */
                    $promoCodeData = $form->getData();

                    if ($promoCodeData->discountType === PromoCode::DISCOUNT_TYPE_NOMINAL) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_NOMINAL;
                    } else {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_PERCENT;
                    }

                    return $validationGroups;
                },
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield PromoCodeFormType::class;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateDateTimeFrom(PromoCodeData $promoCodeData, ExecutionContextInterface $context): void
    {
        if ($promoCodeData->timeValidFrom !== null &&
            $promoCodeData->dateValidFrom === null
        ) {
            $context->buildViolation(t('If the time FROM is filled in, enter the date FROM too.'))
                ->atPath('dateValidFrom')
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateDateTimeTo(PromoCodeData $promoCodeData, ExecutionContextInterface $context): void
    {
        if ($promoCodeData->timeValidTo !== null &&
            $promoCodeData->dateValidTo === null
        ) {
            $context->buildViolation(t('If the time TO is filled in, enter the date TO too.'))
                ->atPath('dateValidTo')
                ->addViolation();
        }
    }

    /**
     * @param string|null $time
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateTimeIfIsSet(?string $time, ExecutionContextInterface $context): void
    {
        if ($time !== null &&
            $time !== '' &&
            preg_match(DateTimeHelper::TIME_REGEX, $time) !== 1
        ) {
            $context->addViolation(t('Please enter the correct time format (hh:mm)'));
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniquePromoCodeByDomain(
        PromoCodeData $promoCodeData,
        ExecutionContextInterface $context,
    ): void {
        if ($promoCodeData->code === null) {
            return;
        }
        if ($this->promoCode !== null && $promoCodeData->code === $this->promoCode->getCode()) {
            return;
        }

        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeData->code, $this->getDomainId());
        if ($promoCode !== null) {
            $context->buildViolation('Promo code with this code already exists')->atPath('code')->addViolation();
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function addMassGenerationGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderMassPromoCodeGroup = $builder->create('massPromoCodeGroup', GroupType::class, [
            'label' => t('Bulk promo code generation'),
            'position' => 'first',
        ]);

        $builderMassPromoCodeGroup
            ->add('prefix', TextType::class, [
                'label' => t('Prefix (e.g. "SPRING_")'),
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => t('Number of generated promo codes'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the quantity.',
                    ]),
                    new Positive([
                        'message' => 'Please enter the positive value.',
                    ]),
                ],
                'invalid_message' => 'Please enter the whole number.',
            ]);

        return $builderMassPromoCodeGroup;
    }

    /**
     * @return int
     */
    private function getDomainId(): int
    {
        if ($this->promoCode !== null) {
            return $this->promoCode->getDomainId();
        }

        return $this->adminDomainTabsFacade->getSelectedDomainId();
    }
}
