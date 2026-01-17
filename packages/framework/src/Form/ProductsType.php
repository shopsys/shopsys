<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductsType extends AbstractType
{
    public function __construct(private readonly ProductsIdsToProductsTransformer $productsIdsToProductsTransformer)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->productsIdsToProductsTransformer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['products'] = $form->getData();
        $view->vars['main_product'] = $options['main_product'];
        $view->vars['sortable'] = $options['sortable'];
        $view->vars['allow_main_variants'] = (int)$options['allow_main_variants'];
        $view->vars['allow_variants'] = (int)$options['allow_variants'];
        $view->vars['label_button_add'] = $options['label_button_add'];
        $view->vars['top_info_title'] = $options['top_info_title'];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
            'renders_in_own_card' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
