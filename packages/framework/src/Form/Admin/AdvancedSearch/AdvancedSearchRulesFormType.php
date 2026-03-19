<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AdvancedSearchRulesFormType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entity_type');
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entity_type'] = $options['entity_type'];
    }
}
