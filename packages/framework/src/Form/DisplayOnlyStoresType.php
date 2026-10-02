<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DisplayOnlyStoresType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('stores')
            ->setAllowedTypes('stores', 'array')
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'compound' => false,
            ]);
    }

    /**
     * @param array{stores: \Shopsys\FrameworkBundle\Model\Store\Store[]} $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['stores'] = $options['stores'];
    }
}
