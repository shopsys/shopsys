<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ProductReviewFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(ProductReviewData $productReviewData): ProductReview
    {
        $entityClassName = $this->entityNameResolver->resolve(ProductReview::class);

        return new $entityClassName($productReviewData);
    }
}
