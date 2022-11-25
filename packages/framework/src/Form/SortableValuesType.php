<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortableValuesType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => HiddenType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'labels_by_value' => null,
            'placeholder' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['labels_by_value'] = $options['labels_by_value'];
        $view->vars['placeholder'] = $options['placeholder'];
    }
}
