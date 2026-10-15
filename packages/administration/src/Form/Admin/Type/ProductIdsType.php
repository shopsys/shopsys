<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * ProductsType works with product entities, the content document stores product ids,
 * so the ids are turned into products before they get to the parent type and back after it.
 * Products that no longer exist are dropped.
 */
final class ProductIdsType extends AbstractType
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (mixed $ids): array => $this->findProducts($ids),
            static fn (mixed $products): array => array_map(static fn ($product): int => $product->getId(), is_array($products) ? $products : []),
        ));
    }

    // ProductsType renders the model data, which are ids here
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['products'] = $this->findProducts($form->getData());
    }

    #[Override]
    public function getParent(): string
    {
        return ProductsType::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    private function findProducts(mixed $ids): array
    {
        if (!is_array($ids) || $ids === []) {
            return [];
        }

        $productsById = [];

        foreach ($this->productRepository->getAllByIds(array_map('intval', $ids)) as $product) {
            $productsById[$product->getId()] = $product;
        }

        $products = [];

        foreach ($ids as $id) {
            if (isset($productsById[(int)$id])) {
                $products[] = $productsById[(int)$id];
            }
        }

        return $products;
    }
}
