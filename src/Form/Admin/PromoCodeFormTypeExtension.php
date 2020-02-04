<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\PromoCodeFormType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PromoCodeFormTypeExtension extends AbstractTypeExtension
{
    protected const TIME_REGEX = '#^([01]?[0-9]|2[0-3]):[0-5][0-9]$#'; //hh:mm

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCode|null
     */
    private $promoCode;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(PromoCodeFacade $promoCodeFacade)
    {
        $this->promoCodeFacade = $promoCodeFacade;
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
                'label' => t('Domain'),
            ]);
        }

        $codeOptions = $builder->get('code')->getOptions();
        $codeOptions['constraints'] = [
            new Constraints\NotBlank(['message' => 'Please enter code']),
        ];
        $codeOptions['position'] = 'first';

        $builder->add('code', TextType::class, $codeOptions);

        $this->buildTimeValidationForm($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildTimeValidationForm(FormBuilderInterface $builder)
    {
        $timeOptions = [
            'icon_title' => t('Fotmát času: \'hh:mm\', např.: \'07:45\',\'23:05\''),
            'constraints' => [
                new Constraints\Callback([$this, 'validateTimeIfIsSet']),
            ],
        ];

        $dateOptions = [
            'view_timezone' => 'UTC',
        ];

        $builder->add('dateValidFrom', DatePickerType::class, $dateOptions);
        $builder->add('timeValidFrom', TextType::class, $timeOptions);
        $builder->add('dateValidTo', DatePickerType::class, $dateOptions);
        $builder->add('timeValidTo', TextType::class, $timeOptions);
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
        if ($promoCodeData->timeValidFrom != null) {
            if ($promoCodeData->dateValidFrom == null) {
                $context->buildViolation('Pokud je vyplněn čas OD, vyplň i datum OD.')
                    ->atPath('date_valid_from')
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
        if ($promoCodeData->timeValidTo != null) {
            if ($promoCodeData->dateValidTo == null) {
                $context->buildViolation('Pokud je vyplněn čas DO, vyplň i datum DO.')
                    ->atPath('date_valid_to')
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
        if ($time != null || $time != '') {
            if (!(bool)preg_match(self::TIME_REGEX, $time)) {
                $context->addViolation('Špatný formát času: ' . $time . ', správně: hh:mm.');
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
                $context->buildViolation('Promo code with this code already exists')->atPath('code')->addViolation();
            }
        }
    }
}
