<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LinkType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['link', 'label'])
            ->setAllowedTypes('link', ['string'])
            ->setAllowedTypes('label', ['string'])
            ->setDefaults([
                'mapped' => false,
            ]);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['link'] = $options['link'];
        $view->vars['label'] = $options['label'];
    }
}
