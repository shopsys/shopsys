<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Price;

use Override;
use Shopsys\FrameworkBundle\Form\VatChoiceType;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductPricesWithVatSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vat', VatChoiceType::class, [
                'block_prefix' => 'prices_select_vat_input',
                'domain_id' => $options['domain_id'],
            ])
            ->add(
                'manualInputPricesByPricingGroupId',
                PricesByPricingGroupsType::class,
                [
                    'label' => false,
                    'required' => false,
                    'domain_id' => $options['domain_id'],
                    'product_prices' => $options['product_prices'],
                ],
            );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'product_prices' => null,
                'data_class' => ProductInputPriceData::class,
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
    }
}
