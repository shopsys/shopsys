<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\IdsToEntitiesTransformer;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionDataProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TreeSelectionType extends AbstractType
{
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $selectedEntities = $form->getData() ?? [];
        $selectedIds = array_map('intval', $form->getViewData() ?? []);

        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['tree_items'] = $options['data_provider']->getCollapsedTree($selectedEntities);
        $view->vars['selected_ids'] = $selectedIds;
        $view->vars['checkbox_name'] = $view->vars['full_name'] . '[]';
        $view->vars['checkbox_id_prefix'] = $view->vars['id'];
        $view->vars['branch_load_route'] = $options['branch_load_route'];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IdsToEntitiesTransformer($options['data_provider']));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['branch_load_route', 'domain_id', 'data_provider'])
            ->setAllowedTypes('branch_load_route', 'string')
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('data_provider', TreeSelectionDataProviderInterface::class)
            ->setDefaults([
                'required' => false,
                'compound' => false,
                'multiple' => true,
                'empty_data' => [],
            ]);
    }
}
