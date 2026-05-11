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
        $collapsedTree = $options['data_provider']->getCollapsedTree($selectedEntities);

        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['tree_items'] = $this->buildTreeItems($collapsedTree);
        $view->vars['selected_ids'] = $selectedIds;
        $view->vars['checkbox_name'] = $view->vars['full_name'] . '[]';
        $view->vars['checkbox_id_prefix'] = $view->vars['id'];
        $view->vars['branch_load_route'] = $options['branch_load_route'];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[] $items
     * @return array<int, array{item: \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface, children: array}>
     */
    protected function buildTreeItems(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $items = array_values($items);
        $index = 0;

        return $this->buildTreeItemsForLevel($items, $index, $items[0]->getLevel());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[] $items
     * @return array<int, array{item: \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface, children: array}>
     */
    protected function buildTreeItemsForLevel(array $items, int &$index, int $level): array
    {
        $nodes = [];

        while (isset($items[$index]) && $items[$index]->getLevel() === $level) {
            $item = $items[$index];
            $index++;

            $nodes[] = [
                'item' => $item,
                'children' => $this->buildTreeItemsForLevel($items, $index, $level + 1),
            ];
        }

        return $nodes;
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
            ->setDefined('domain_id')
            ->setRequired(['branch_load_route', 'data_provider'])
            ->setAllowedTypes('branch_load_route', 'string')
            ->setAllowedTypes('domain_id', ['int', 'null'])
            ->setAllowedTypes('data_provider', TreeSelectionDataProviderInterface::class)
            ->setDefaults([
                'required' => false,
                'compound' => false,
                'multiple' => true,
                'empty_data' => [],
                'domain_id' => null,
            ]);
    }
}
