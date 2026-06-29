<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductIdToProductTransformer extends AbstractEntityIdToEntityTransformer
{
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    #[Override]
    protected function getEntityId(object $entity): int
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $entity;

        return $product->getId();
    }

    #[Override]
    protected function getEntityById(int $entityId): Product
    {
        try {
            return $this->productRepository->getById($entityId);
        } catch (ProductNotFoundException $exception) {
            throw new TransformationFailedException('Product not found', 0, $exception);
        }
    }
}
