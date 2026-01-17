<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\BlogCategoriesTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlogCategoriesType extends AbstractType
{
    public function __construct(
        protected readonly BlogCategoriesTypeTransformer $blogCategoriesTypeTransformer,
    ) {
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domain_id'] = $options['domain_id'];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this->blogCategoriesTypeTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['domain_id'] = $value['domain_id'] ?? $options['domain_id'];

            return $value;
        };

        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'required' => false,
                'entry_type' => BlogCategoryCheckboxType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
