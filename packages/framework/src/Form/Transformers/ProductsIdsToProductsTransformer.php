<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductsIdsToProductsTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[]|null $products
     * @return int[]
     */
    public function transform($products): array
    {
        $productsIds = [];

        if (is_iterable($products)) {
            foreach ($products as $key => $product) {
                $productsIds[$key] = $product->getId();
            }
        }

        return $productsIds;
    }

    /**
     * @param int[] $productsIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function reverseTransform($productsIds): array
    {
        $products = [];

        if (is_array($productsIds)) {
            foreach ($productsIds as $key => $productId) {
                try {
                    $products[$key] = $this->productRepository->getById($productId);
                } catch (ProductNotFoundException $e) {
                    throw new TransformationFailedException('Product not found', 0, $e);
                }
            }
        }

        return $products;
    }
}
