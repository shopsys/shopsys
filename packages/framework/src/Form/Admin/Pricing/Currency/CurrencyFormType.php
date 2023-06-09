<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Pricing\Currency;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CurrencyFormType extends AbstractType
{
    /**
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly CurrencyRepositoryInterface $intlCurrencyRepository,
        private readonly Localization $localization
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \CommerceGuys\Intl\Currency\Currency[] $intlCurrencies */
        $intlCurrencies = $this->intlCurrencyRepository->getAll($this->localization->getLocale());

        $possibleCurrencyCodes = [];
        foreach ($intlCurrencies as $intlCurrency) {
            $possibleCurrencyCodes[] = $intlCurrency->getCurrencyCode();
        }

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(
                        ['max' => 50, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']
                    ),
                ],
            ])
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter currency code']),
                    new Constraints\Choice([
                        'choices' => $possibleCurrencyCodes,
                        'message' => 'Please enter valid 3-digit currency code according to ISO 4217 standard (uppercase)',
                    ]),
                ],
            ])
            ->add('exchangeRate', NumberType::class, [
                'required' => true,
                'scale' => 6,
                'attr' => [
                    'readonly' => $options['is_default_currency'],
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter currency exchange rate']),
                    new Constraints\GreaterThan(0),
                ],
            ])
            ->add('minFractionDigits', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter currency minimum fraction digits']),
                    new Constraints\GreaterThanOrEqual(0),
                    new Constraints\LessThanOrEqual(CurrencyFormatterFactory::MAXIMUM_FRACTION_DIGITS),
                ],
            ])
            ->add('roundingType', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('To hundredths (cents)') => Currency::ROUNDING_TYPE_HUNDREDTHS,
                    t('To fifty hundredths (halfs)') => Currency::ROUNDING_TYPE_FIFTIES,
                    t('To whole numbers') => Currency::ROUNDING_TYPE_INTEGER,
                ],
                'label' => t('Price including VAT rounding'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('is_default_currency')
            ->setAllowedTypes('is_default_currency', 'bool')
            ->setDefaults([
                'data_class' => CurrencyData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
