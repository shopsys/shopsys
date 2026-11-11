<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm;

use Override;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormType;

/**
 * Orders the children of the form view by their `position` option, see docs/extensibility/form-extension.md
 *
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
class OrderedResolvedFormType extends ResolvedFormType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $unorderedChildren = $view->children;
        $view->children = [];

        foreach ($this->createSorter($form)->getSortedFieldNames() as $fieldName) {
            if (isset($unorderedChildren[$fieldName])) {
                $view->children[$fieldName] = $unorderedChildren[$fieldName];
            }
        }

        // keep the children that are not fields of the form itself (e.g. _token)
        foreach ($unorderedChildren as $fieldName => $child) {
            if (!isset($view->children[$fieldName])) {
                $view->children[$fieldName] = $child;
            }
        }
    }

    protected function createSorter(FormInterface $form): FormFieldSorter
    {
        $sorter = new FormFieldSorter();

        foreach ($form as $child) {
            $sorter->add($child->getName(), $child->getConfig()->getOption('position'));
        }

        return $sorter;
    }
}
