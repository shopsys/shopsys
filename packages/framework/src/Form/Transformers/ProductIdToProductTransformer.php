<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductIdToProductTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     * @return int|null
     */
    public function transform($product): ?int
    {
        if ($product instanceof Product) {
            return $product->getId();
        }

        return null;
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function reverseTransform($productId): ?Product
    {
        if ((int)$productId === 0) {
            return null;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (ProductNotFoundException $e) {
            throw new TransformationFailedException('Product not found', 0, $e);
        }

        return $product;
    }
}
