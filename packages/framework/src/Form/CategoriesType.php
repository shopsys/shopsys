<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Transformers\CategoriesTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoriesType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\CategoriesTypeTransformer
     */
    private $categoriesTypeTransformer;

    public function __construct(CategoriesTypeTransformer $categoryTransformer)
    {
        $this->categoriesTypeTransformer = $categoryTransformer;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['domain_id'] = $options['domain_id'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this->categoriesTypeTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
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
                'entry_type' => CategoryCheckboxType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
