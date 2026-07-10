<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\Constraint\UniqueFlags;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\Form\AbstractType;
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

final class PromoCodeFormType extends AbstractType
{
    public const string VALIDATION_GROUP_TYPE_PERCENT = 'type_percent';
    public const string VALIDATION_GROUP_TYPE_NOMINAL = 'type_nominal';

    private ?PromoCode $promoCode = null;

    public function __construct(
        private readonly PromoCodeFacade $promoCodeFacade,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly BrandFacade $brandFacade,
        private readonly PromoCodeTypeEnum $promoCodeTypeEnum,
        private readonly GiftVoucherFacade $giftVoucherFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->promoCode = $options['promo_code'];

        if ($options['mass_generate']) {
            $builder->add($this->addMassGenerationGroup($builder));
            $builder->remove('code');
        }

        $this->buildBaseGroup($builder, $options);
        $this->buildLimitsFormGroup($builder);
        $this->buildTimeValidationFormGroup($builder);
        $this->buildFlagsFormGroup($builder);
        $this->buildCustomersFormGroup($builder);
        $this->buildProductsWithSaleForm($builder);
        $this->buildCategoriesWithSaleFormGroup($builder);
        $this->buildBrandsWithSaleFormGroup($builder);

        $actionBar = $builder->create('actionBar', ActionBarType::class, [
            'back_route' => 'admin_promocode_list',
            'entity' => $this->promoCode,
        ]);

        if ($options['mass_generate']) {
            $actionBar->add('saveAndDownloadCsv', SubmitType::class, [
                'label' => 'Create and download CSV',
                'position' => [
                    'before' => 'save',
                ],
            ]);
        }

        $builder->add($actionBar);
    }

    private function buildBaseGroup(FormBuilderInterface $builder, array $options): void
    {
        $baseGroup = $builder->create('baseGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if (!$options['mass_generate']) {
            $baseGroup
                ->add('code', TextType::class, [
                    'label' => 'Promo code',
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter promo code'),
                    ],
                ]);
        }

        if ($this->promoCode instanceof PromoCode) {
            $baseGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $this->promoCode->getId(),
            ]);
        }

        $baseGroup->add('domainId', HiddenType::class, [
            'data' => $this->getDomainId(),
        ])
            ->add('shownDomainId', DomainType::class, [
                'mapped' => false,
                'label' => 'Domain',
                'disabled' => true,
            ])
            ->add('discountType', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $this->promoCodeTypeEnum->getAllIndexedByTranslations(),
                'label' => 'Discount type',
                'attr' => [
                    'class' => 'js-promo-code-discount-type',
                ],
            ])
            ->add('remainingUses', IntegerType::class, [
                'label' => 'Remaining number of uses',
                'required' => false,
                'constraints' => [
                    new Constraints\GreaterThanOrEqual('0'),
                ],
            ])
            ->add('enabled', YesNoType::class, [
                'label' => 'Enabled',
            ]);

        $builder->add($baseGroup);
    }

    private function buildLimitsFormGroup(FormBuilderInterface $builder): void
    {
        $discountOptions = [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(
                    message: 'Please enter discount percentage',
                    groups: [self::VALIDATION_GROUP_TYPE_PERCENT, self::VALIDATION_GROUP_TYPE_NOMINAL],
                ),
                new Constraints\Range(
                    min: 0,
                    max: 100,
                    groups: [self::VALIDATION_GROUP_TYPE_PERCENT, self::VALIDATION_GROUP_TYPE_NOMINAL],
                ),
            ],
            'invalid_message' => 'Please enter number.',
            'label' => 'Discount (%)',
        ];

        $limitsGroup = $builder->create('limitsGroup', GroupType::class, [
            'label' => 'Apply according to the total price of the order',
            'row_attr' => [
                'data-js-promo-code-limits-group' => null,
            ],
        ]);

        $limitsGroup->add(
            $limitsGroup->create('limits', PromoCodeLimitCollectionType::class, [
                'label' => false,
                'entry_type' => PromoCodeLimitType::class,
                'entry_options' => ['discount' => $discountOptions],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'constraints' => [
                    new Constraints\Count(
                        min: 1,
                        minMessage: 'Please enter at least one discount limit',
                        groups: [self::VALIDATION_GROUP_TYPE_PERCENT, self::VALIDATION_GROUP_TYPE_NOMINAL],
                    ),
                ],
            ]),
        );

        $builder->add($limitsGroup);
    }

    private function buildTimeValidationFormGroup(FormBuilderInterface $builder): void
    {
        $timeValidationGroup = $builder->create('timeValidationGroup', GroupType::class, [
            'label' => 'Apply according to date and time limit',
        ]);

        $timeValidationGroup->add('datetimeValidFrom', DateTimeType::class, [
            'required' => false,
            'label' => 'Valid from',
        ])->add('datetimeValidTo', DateTimeType::class, [
            'required' => false,
            'label' => 'Valid to',
        ]);

        $builder->add($timeValidationGroup);
    }

    private function buildFlagsFormGroup(FormBuilderInterface $builder): void
    {
        $flagsGroup = $builder->create('flagsGroup', GroupType::class, [
            'label' => 'Apply according to product flags',
        ]);

        $flagsGroup->add('flags', PromoCodeFlagCollectionType::class, [
            'label' => false,
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

    private function buildCustomersFormGroup(FormBuilderInterface $builder): void
    {
        $customersGroup = $builder->create('customersGroup', GroupType::class, [
            'label' => 'Apply according to customer',
        ]);
        $builder->add($customersGroup);
        $customersGroup->add('registeredCustomerUserOnly', YesNoType::class, [
            'label' => 'For registered customers only',
        ])
            ->add('limitedPricingGroups', ChoiceType::class, [
                'required' => false,
                'choices' => $this->pricingGroupFacade->getByDomainId($this->adminDomainTabsFacade->getSelectedDomainId()),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Pricing groups',
                'multiple' => true,
            ]);
    }

    private function buildProductsWithSaleForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('productsWithSale', ProductsType::class, [
                'required' => false,
                'sortable' => true,
                'label' => 'Apply to selected products',
            ]);
    }

    private function buildCategoriesWithSaleFormGroup(FormBuilderInterface $builder): void
    {
        $displayCategoriesGroup = $builder->create('displayCategoriesGroup', GroupType::class, [
            'label' => 'Apply to selected categories',
        ]);
        $displayCategoriesGroup->add('categoriesWithSale', CategoriesType::class, [
            'required' => false,
            'domain_id' => $this->getDomainId(),
            'label' => false,
        ]);
        $builder->add($displayCategoriesGroup);
    }

    private function buildBrandsWithSaleFormGroup(FormBuilderInterface $builder): void
    {
        $displayCategoriesGroup = $builder->create('displayBrandsGroup', GroupType::class, [
            'label' => 'Apply to selected brands',
        ]);
        $displayCategoriesGroup->add('brandsWithSale', ChoiceType::class, [
            'required' => false,
            'choices' => $this->brandFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'label' => false,
            'multiple' => true,
        ]);
        $builder->add($displayCategoriesGroup);
    }

    private function addMassGenerationGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderMassPromoCodeGroup = $builder->create('massPromoCodeGroup', GroupType::class, [
            'label' => 'Bulk promo code generation',
        ]);

        $builderMassPromoCodeGroup
            ->add('prefix', TextType::class, [
                'label' => 'Prefix (e.g. "SPRING_")',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Number of generated promo codes',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter the quantity.'),
                    new Positive(message: 'Please enter the positive value.'),
                ],
                'invalid_message' => 'Please enter the whole number.',
            ]);

        return $builderMassPromoCodeGroup;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['promo_code', 'mass_generate'])
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setAllowedTypes('mass_generate', 'bool')
            ->setDefaults([
                'mass_generate' => false,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback(callback: [$this, 'validateUniquePromoCodeByDomain']),
                ],
                'validation_groups' => static function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
                    /** @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData */
                    $promoCodeData = $form->getData();

                    if ($promoCodeData->discountType === PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_NOMINAL;
                    } elseif ($promoCodeData->discountType === PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_PERCENT;
                    }

                    return $validationGroups;
                },
            ]);
    }

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

        if ($this->giftVoucherFacade->findByCode($promoCodeData->code) !== null) {
            $context->buildViolation('A gift voucher with this code already exists')->atPath('code')->addViolation();
        }
    }

    private function getDomainId(): int
    {
        if ($this->promoCode !== null) {
            return $this->promoCode->getDomainId();
        }

        return $this->adminDomainTabsFacade->getSelectedDomainId();
    }
}
