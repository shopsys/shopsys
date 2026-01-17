<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductType extends AbstractType
{
    public function __construct(
        private readonly ProductIdToProductTransformer $productIdToProductTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->productIdToProductTransformer);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
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
    #[Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'placeholder' => 'Choose product',
            'enableRemove' => false,
            'required' => true,
            'allow_main_variants' => true,
            'allow_variants' => true,
        ]);
    }
}
