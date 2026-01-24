<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Price;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PricingGroupPriceType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'product_price' => null,
        ]);
        $resolver->setAllowedTypes('product_price', [ProductPriceInterface::class, 'null']);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['product_price'] = $options['product_price'];
    }

    #[Override]
    public function getParent(): string
    {
        return MoneyType::class;
    }
}
