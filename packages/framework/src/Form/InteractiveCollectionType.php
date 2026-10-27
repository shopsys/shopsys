<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InteractiveCollectionType extends AbstractType
{
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['add_label'] = $options['add_label'];
        $view->vars['empty_message'] = $options['empty_message'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'add_label' => t('Add item'),
                'empty_message' => null,
            ])
            ->setAllowedTypes('add_label', 'string')
            ->setAllowedTypes('empty_message', ['string', 'null']);
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
