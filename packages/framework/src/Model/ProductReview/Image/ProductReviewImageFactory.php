<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;

class ProductReviewImageFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        ProductReview $productReview,
        ProductReviewImageData $productReviewImageData,
        int $position,
    ): ProductReviewImage {
        $entityClassName = $this->entityNameResolver->resolve(ProductReviewImage::class);

        return new $entityClassName($productReview, $productReviewImageData, $position);
    }
}
