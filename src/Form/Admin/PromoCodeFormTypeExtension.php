<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\DateTimeHelper\DateTimeHelper;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
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

        if ($this->promoCode === null) {
            $builder->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Doména'),
            ]);
        }

        $discountOptions = $builder->get('percent')->getOptions();
        $discountOptions['label'] = t('Sleva (%)');
        $builder->add('percent', IntegerType::class, $discountOptions);

        $codeOptions = $builder->get('code')->getOptions();
        $codeOptions['constraints'] = [
            new Constraints\NotBlank(['message' => 'Prosím vložte kód']),
        ];
        $codeOptions['position'] = 'first';
        $codeOptions['label'] = t('Promo kód');
        $builder->add('code', TextType::class, $codeOptions);

        $this->buildTimeValidationForm($builder);

        $this->buildProductsWithSaleForm($builder);
        $this->buildCategoriesWithSaleForm($builder);
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
            ->setAllowedTypes('promo_code', [PromoCode::class, 'null'])
            ->setDefaults([
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUniquePromoCodeByDomain']),
                    new Constraints\Callback([$this, 'validateDateTimeFrom']),
                    new Constraints\Callback([$this, 'validateDateTimeTo']),
                ],
            ]);
    }

    public function getExtendedType()
    {
        return PromoCodeFormType::class;
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
        if ($this->promoCode === null || $promoCodeData->code !== $this->promoCode->getCode()) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeData->code, $promoCodeData->domainId);
            if ($promoCode !== null) {
                $context->buildViolation('Promo kód s tímto kódem již existuje')->atPath('code')->addViolation();
            }
        }
    }
}
