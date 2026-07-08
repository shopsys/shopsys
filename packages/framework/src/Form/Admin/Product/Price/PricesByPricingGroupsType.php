<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Price;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Form\Constraints\PricesForAllCurrenciesOrNone;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PricesByPricingGroupsType extends AbstractType
{
    public function __construct(
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly Domain $domain,
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[] $productPrices */
        $productPrices = $options['product_prices'];
        $currencyCodes = $this->getEditableCurrencyCodes($options['domain_id']);

        foreach ($this->pricingGroupFacade->getByDomainId($options['domain_id']) as $pricingGroup) {
            $builder->add($this->createPricingGroupBuilder($builder, $pricingGroup, $currencyCodes, $productPrices));
        }
    }

    /**
     * @param string[] $currencyCodes
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[]|null $productPrices
     */
    private function createPricingGroupBuilder(
        FormBuilderInterface $builder,
        PricingGroup $pricingGroup,
        array $currencyCodes,
        ?array $productPrices,
    ): FormBuilderInterface {
        $hasMultipleCurrencies = count($currencyCodes) > 1;

        $pricingGroupBuilder = $builder->create((string)$pricingGroup->getId(), FormType::class, [
            'label' => $hasMultipleCurrencies ? $pricingGroup->getName() : false,
            'block_prefix' => 'pricing_group_currency_prices',
            'required' => false,
            'constraints' => $hasMultipleCurrencies ? [
                new PricesForAllCurrenciesOrNone(),
            ] : [],
        ]);

        $defaultCurrencyCode = $currencyCodes[0];

        foreach ($currencyCodes as $currencyCode) {
            $pricingGroupBuilder->add($currencyCode, PricingGroupPriceType::class, [
                'product_price' => $currencyCode === $defaultCurrencyCode && $productPrices !== null ? $productPrices[$pricingGroup->getId()] : null,
                'currency_code' => $currencyCode,
                'block_prefix' => 'pricing_group_price_input',
                'scale' => 6,
                'required' => false,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new NotNegativeMoneyAmount(message: 'Price must be greater or equal to zero'),
                ],
                'label' => $hasMultipleCurrencies ? $currencyCode : $pricingGroup->getName(),
            ]);
        }

        return $pricingGroupBuilder;
    }

    /**
     * @return string[]
     */
    private function getEditableCurrencyCodes(int $domainId): array
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            return $domainConfig->getCurrencyCodes();
        }

        return [$domainConfig->getDefaultCurrencyCode()];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'product_prices' => null,
            ])
            ->setRequired(['domain_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('product_prices', ['array', 'null']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['product_prices'] = $options['product_prices'];
    }
}
