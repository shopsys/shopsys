<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductsIdsToProductsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

/**
 * ProductsType works with product entities, the content document stores product ids,
 * so the ids are turned into products before they get to the parent type and back after it.
 */
final class ProductIdsType extends AbstractType
{
    public function __construct(
        private readonly ProductsIdsToProductsTransformer $productsIdsToProductsTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ReversedTransformer($this->productsIdsToProductsTransformer), true);
    }

    #[Override]
    public function getParent(): string
    {
        return ProductsType::class;
    }
}
