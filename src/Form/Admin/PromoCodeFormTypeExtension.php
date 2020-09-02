<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\DateTimeHelper\DateTimeHelper;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCode|null
     */
    private $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer
     */
    private $removeDuplicatesTransformer;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->removeDuplicatesTransformer = $removeDuplicatesTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->promoCode = $options['promo_code'];

        if ($options['mass_generate'] === true) {
            $builder->add($this->addMassGenerationGroup($builder));
            $builder->add('saveAndDownloadCsv', SubmitType::class, [
                'label' => t('Vytvořit a stáhnout CSV'),
            ]);
        }

        if ($this->promoCode === null) {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Doména'),
            ]);
        }

        $this->buildBaseFormGroup($builder, $options);
        $this->buildPromoCodeFlagsForm($builder);
        $this->buildProductsWithSaleForm($builder);
        $this->buildCategoriesWithSaleForm($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    private function buildBaseFormGroup(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('discountType', ChoiceType::class, [
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                t('Procenta') => PromoCode::DISCOUNT_TYPE_PERCENT,
                t('Nominální') => PromoCode::DISCOUNT_TYPE_NOMINAL,
            ],
            'position' => ['before' => 'limits'],
            'label' => t('Typ slevy'),
        ]);

        $discountOptions = $builder->get('percent')->getOptions();
        $discountOptions['label'] = t('Sleva (%)');
        $builder->remove('percent');

        $putLimitsAfter = $options['mass_generate'] ? 'identifier' : 'code';
        $this->buildLimitFields($builder, $discountOptions, $putLimitsAfter);

        $builder->add('identifier', TextType::class, [
            'label' => t('Identifikátor kupónu pro IS'),
            'required' => true,
            'constraints' => [
                new Constraints\NotNull([
                    'message' => t('Identifikátor musí obsahovat dva znaky'),
                ]),
                new Constraints\Length([
                    'min' => 2,
                    'max' => 2,
                    'exactMessage' => t('Identifikátor musí obsahovat dva znaky'),
                ]),
            ],
        ]);

        $builder->add('applyOnSecondProduct', YesNoType::class, [
            'label' => t('Platí na druhý produkt v košíku'),
            'required' => false,
            'position' => ['after' => 'limits'],
        ]);

        $codeOptions = $builder->get('code')->getOptions();
        $codeOptions['constraints'] = [
            new Constraints\NotBlank(['message' => 'Vyplňte prosím promo kód']),
        ];
        $codeOptions['position'] = 'first';
        $codeOptions['label'] = t('Promo kód');
        $builder->add('code', TextType::class, $codeOptions);

        if ($options['mass_generate'] === true) {
            $builder->remove('code');
        }

        $this->buildTimeValidationForm($builder);

        $builder->add('remainingUses', IntegerType::class, [
            'label' => t('Zbývající počet použití'),
            'required' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildPromoCodeFlagsForm(FormBuilderInterface $builder): void
    {
        $flagsGroup = $builder->create('flagsGroup', GroupType::class, [
            'label' => t('Aplikovat podle příznaků'),
        ]);
        $builder->add($flagsGroup);
        $flagsGroup->add('onSale', YesNoType::class, [
            'required' => false,
            'label' => t('Produkt ve výprodeji'),
        ])
        ->add('inAction', YesNoType::class, [
            'required' => false,
            'label' => t('Produkt v akci'),
        ])
        ->add('scontoPrice', YesNoType::class, [
            'required' => false,
            'label' => t('Produkt se sconto cenou'),
        ])
        ->add('withoutLowPrice', YesNoType::class, [
            'required' => false,
            'label' => t('Produkt bez nižší ceny'),
        ])
        ->add('priceHit', YesNoType::class, [
            'required' => false,
            'label' => t('Produkt cenový hit'),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildTimeValidationForm(FormBuilderInterface $builder)
    {
        $builder->add('dateValidFrom', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum platnosti OD'),
        ])->add('timeValidFrom', TextType::class, [
            'icon_title' => t('Formát času: "hh:mm", např.: "07:45", "23:05"'),
            'constraints' => [
                new Constraints\Callback([$this, 'validateTimeIfIsSet']),
            ],
            'required' => false,
            'label' => t('Čas platnosti OD'),
        ])->add('dateValidTo', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum platnosti DO'),
        ])->add('timeValidTo', TextType::class, [
            'icon_title' => t('Formát času: "hh:mm", např.: "07:45", "23:05"'),
            'constraints' => [
                new Constraints\Callback([$this, 'validateTimeIfIsSet']),
            ],
            'required' => false,
            'label' => t('Čas platnosti DO'),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildCategoriesWithSaleForm(FormBuilderInterface $builder)
    {
        $displayCategoriesGroup = $builder->create('displayCategoriesGroup', GroupType::class, [
            'label' => t('Slevněné kategorie'),
        ]);
        $displayCategoriesGroup->add('categoriesWithSale', CategoriesType::class, [
            'required' => false,
            'domain_id' => Domain::FIRST_DOMAIN_ID,
            'label' => t('Kategorie'),
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
        ]);
        $builder->add($displayCategoriesGroup);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildProductsWithSaleForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('productsWithSale', ProductsType::class, [
                'required' => false,
                'sortable' => true,
                'label' => t('Slevněné produkty'),
            ])
            ->addViewTransformer($this->removeDuplicatesTransformer);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
    public function validateDateTimeFrom(PromoCodeData $promoCodeData, ExecutionContextInterface $context)
    {
        if ($promoCodeData->timeValidFrom !== null) {
            if ($promoCodeData->dateValidFrom === null) {
                $context->buildViolation(t('Pokud je vyplněn čas OD, vyplňte i datum OD.'))
                    ->atPath('dateValidFrom')
                    ->addViolation();
            }
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateDateTimeTo(PromoCodeData $promoCodeData, ExecutionContextInterface $context)
    {
        if ($promoCodeData->timeValidTo !== null) {
            if ($promoCodeData->dateValidTo === null) {
                $context->buildViolation(t('Pokud je vyplněn čas DO, vyplňte i datum DO.'))
                    ->atPath('dateValidTo')
                    ->addViolation();
            }
        }
    }

    /**
     * @param string|null $time
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateTimeIfIsSet($time, ExecutionContextInterface $context)
    {
        if ($time !== null && $time !== '') {
            if (preg_match(DateTimeHelper::TIME_REGEX, $time) !== 1) {
                $context->addViolation(t('Prosím vyplňte správný formát času hh:mm'));
            }
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniquePromoCodeByDomain(PromoCodeData $promoCodeData, ExecutionContextInterface $context)
    {
        if ($promoCodeData->code === null) {
            return;
        }
        if ($this->promoCode === null || $promoCodeData->code !== $this->promoCode->getCode()) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeData->code, $promoCodeData->domainId);
            if ($promoCode !== null) {
                $context->buildViolation('Promo kód s tímto kódem již existuje')->atPath('code')->addViolation();
            }
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function addMassGenerationGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderMassPromoCodeGroup = $builder->create('massPromoCodeGroup', GroupType::class, [
            'label' => t('Hromadné generování kupónu'),
            'position' => 'first',
        ]);

        $builderMassPromoCodeGroup
            ->add('prefix', TextType::class, [
                'label' => t('Prefix (např. "JARO_")'),
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => t('Počet generovaných kupónů'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vyplňte prosím množství.',
                    ]),
                    new Positive([
                        'message' => 'Vyplňte prosím kladnou hodnotu.',
                    ]),
                ],
                'invalid_message' => 'Zadejte prosím celé číslo.',
            ]);

        return $builderMassPromoCodeGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $discountOptions
     * @param string $after
     */
    private function buildLimitFields(FormBuilderInterface $builder, array $discountOptions, string $after): void
    {
        $builder->add(
            $builder->create('limits', PromoCodeLimitCollectionType::class, [
                'label' => t('Limity'),
                'position' => ['after' => $after],
                'entry_type' => PromoCodeLimitType::class,
                'entry_options' => ['discount' => $discountOptions],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'constraints' => [
                    new Constraints\Count([
                        'min' => 1,
                        'minMessage' => t('Vložte, prosím, alespoň jeden limit se slevou'),
                    ]),
                ],
            ])
        );
    }
}
