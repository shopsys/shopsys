<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transport\Price;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Transport\PriceWithLimitData;
use Shopsys\FrameworkBundle\Twig\InputPriceLabelExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PriceWithLimitType extends AbstractType
{
    public function __construct(
        protected readonly InputPriceLabelExtension $inputPriceLabelExtension,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currencyCodes = $this->domain->getDomainConfigById($options['domain_id'])->getCurrencyCodes();

        $pricesByCurrencyCode = $builder->create('pricesByCurrencyCode', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);
        $transportPriceIdsByCurrencyCode = $builder->create('transportPriceIdsByCurrencyCode', FormType::class, [
            'compound' => true,
            'label' => false,
        ]);

        foreach ($currencyCodes as $currencyCode) {
            $pricesByCurrencyCode->add($currencyCode, MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new NotBlank(message: 'Please enter price'),
                ],
                'label' => count($currencyCodes) > 1 ? $currencyCode : $this->inputPriceLabelExtension->getInputPriceLabel(),
            ]);
            $transportPriceIdsByCurrencyCode->add($currencyCode, HiddenType::class);
        }

        $builder
            ->add($pricesByCurrencyCode)
            ->add($transportPriceIdsByCurrencyCode)
            ->add('maxWeight', IntegerType::class);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PriceWithLimitData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setRequired(['domain_id', 'current_transport_prices_indexed_by_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('current_transport_prices_indexed_by_id', 'array');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $currencyCodes = $this->domain->getDomainConfigById($options['domain_id'])->getCurrencyCodes();
        $calculatedPricesByCurrencyCode = [];

        foreach ($form->get('transportPriceIdsByCurrencyCode')->getData() ?? [] as $currencyCode => $transportPriceId) {
            $calculatedPricesByCurrencyCode[$currencyCode] = $options['current_transport_prices_indexed_by_id'][(int)$transportPriceId] ?? null;
        }

        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['currency_codes'] = $currencyCodes;
        $view->vars['transport_calculated_prices_by_currency_code'] = $calculatedPricesByCurrencyCode;
    }
}
