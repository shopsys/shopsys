<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AbstractMultiplePickerType extends AbstractType
{
    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $itemRoute = $options['item_route'];

        $view->vars['items'] = $form->getData();
        $view->vars['sortable'] = $options['sortable'];
        $view->vars['label_button_add'] = $options['label_button_add'];
        $view->vars['top_info_title'] = $options['top_info_title'];
        $view->vars['picker_url'] = $options['picker_url'];
        $view->vars['item_route'] = $itemRoute;

        foreach ($view->vars['items'] as $key => $item) {
            if (is_callable($options['item_name'])) {
                $view->vars['item_names'][$key] = $options['item_name']($item);
            } else {
                $view->vars['item_names'][$key] = $this->propertyAccessor->getValue($item, $options['item_name']);
            }

            if ($itemRoute) {
                $view->vars['item_routes'][$key] = $itemRoute($item);
            }
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => HiddenType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'error_bubbling' => false,
            'sortable' => false,
            'label_button_add' => t('Add'),
            'top_info_title' => '',
            'picker_url' => '',
            'item_name' => '',
            'item_route' => null,
        ]);

        $resolver->setAllowedTypes('picker_url', ['string']);
        $resolver->setRequired('picker_url');

        $resolver->setAllowedTypes('item_name', ['string', 'callable']);
        $resolver->setRequired('item_name');

        $resolver->setAllowedTypes('item_route', ['null', 'callable']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
