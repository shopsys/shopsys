<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductsType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer
     */
    private $productsIdsToProductsTransformer;

    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer $productsIdsToProductsTransformer
     */
    public function __construct(ProductsIdsToProductsTransformer $productsIdsToProductsTransformer)
    {
        $this->productsIdsToProductsTransformer = $productsIdsToProductsTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->productsIdsToProductsTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['products'] = $form->getData();
        $view->vars['main_product'] = $options['main_product'];
        $view->vars['sortable'] = $options['sortable'];
        $view->vars['allow_main_variants'] = (int)$options['allow_main_variants'];
        $view->vars['allow_variants'] = (int)$options['allow_variants'];
        $view->vars['label_button_add'] = $options['label_button_add'];
        $view->vars['top_info_title'] = $options['top_info_title'];
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
            'main_product' => null,
            'error_bubbling' => false,
            'sortable' => false,
            'allow_main_variants' => true,
            'allow_variants' => true,
            'label_button_add' => t('Add product'),
            'top_info_title' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
