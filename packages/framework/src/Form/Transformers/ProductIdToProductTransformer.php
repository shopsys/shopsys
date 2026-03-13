<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductIdToProductTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     */
    #[Override]
    public function transform($product): ?int
    {
        if ($product instanceof Product) {
            return $product->getId();
        }

        return null;
    }

    /**
     * @param int $productId
     */
    #[Override]
    public function reverseTransform($productId): ?Product
    {
        $productId = (int)$productId;

        if ($productId === 0) {
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
