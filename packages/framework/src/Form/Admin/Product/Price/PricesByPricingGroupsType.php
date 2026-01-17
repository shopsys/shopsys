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

final class PricesByPricingGroupsType extends AbstractType
{
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
        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[] $productPrices */
        $productPrices = $options['product_prices'];

        foreach ($this->pricingGroupFacade->getByDomainId($options['domain_id']) as $pricingGroup) {
            $builder->add((string)$pricingGroup->getId(), PricingGroupPriceType::class, [
                'product_price' => $productPrices !== null ? $productPrices[$pricingGroup->getId()] : null,
                'block_prefix' => 'pricing_group_price_input',
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
