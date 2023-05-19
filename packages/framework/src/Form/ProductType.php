<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer $productIdToProductTransformer
     */
    public function __construct(
        private readonly ProductIdToProductTransformer $productIdToProductTransformer,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->productIdToProductTransformer);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['enableRemove'] = $options['enableRemove'];
        $view->vars['allow_main_variants'] = (int)$options['allow_main_variants'];
        $view->vars['allow_variants'] = (int)$options['allow_variants'];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $form->getData();

        if ($product !== null) {
            $view->vars['productName'] = $product->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder' => t('Choose product'),
            'enableRemove' => false,
            'required' => true,
            'allow_main_variants' => true,
            'allow_variants' => true,
        ]);
    }
}
