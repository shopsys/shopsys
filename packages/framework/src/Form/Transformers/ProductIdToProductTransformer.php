<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\DataTransformerInterface;

class ProductIdToProductTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     */
    public function transform(?\Shopsys\FrameworkBundle\Model\Product\Product $product): ?int
    {
        if ($product instanceof Product) {
            return $product->getId();
        }
        return null;
    }
    
    public function reverseTransform(int $productId): ?\Shopsys\FrameworkBundle\Model\Product\Product
    {
        if (empty($productId)) {
            return null;
        }
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException $e) {
            throw new \Symfony\Component\Form\Exception\TransformationFailedException('Product not found', null, $e);
        }
        return $product;
    }
}
