<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PriceListOverviewType extends AbstractType
{
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['specialPrices'] = $options['specialPrices'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['specialPrices'])
            ->setAllowedTypes('specialPrices', 'array')
            ->setDefaults([
                'label' => false,
                'required' => false,
                'mapped' => false,
            ]);
    }
}
