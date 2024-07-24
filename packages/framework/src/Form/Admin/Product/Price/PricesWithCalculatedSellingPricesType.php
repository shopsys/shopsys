<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Price;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PricesWithCalculatedSellingPricesType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     */
    public function __construct(
        protected readonly PricingGroupFacade $pricingGroupFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sellingPrices = $options['selling_prices'];

        foreach ($this->pricingGroupFacade->getByDomainId($options['domain_id']) as $pricingGroup) {
            $builder->add((string)$pricingGroup->getId(), MoneyWithCalculatedPriceType::class, [
                'selling_price' => $sellingPrices !== null ? $sellingPrices[$pricingGroup->getId()]?->getSellingPrice() : null,
                'block_prefix' => 'prices_with_calculated_selling_prices_input',
                'scale' => 6,
                'required' => false,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new NotNegativeMoneyAmount(['message' => 'Price must be greater or equal to zero']),
                ],
                'label' => $pricingGroup->getName(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'selling_prices' => null,
            ])
            ->setRequired(['domain_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('selling_prices', ['array', 'null']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['selling_prices'] = $options['selling_prices'];
    }
}
