<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\ProductReview\Exception\ProductReviewNotFoundException;

class ProductReviewRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getProductReviewRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductReview::class);
    }

    public function getById(int $productReviewId): ProductReview
    {
        $productReview = $this->getProductReviewRepository()->find($productReviewId);

        if ($productReview === null) {
            throw new ProductReviewNotFoundException($productReviewId);
        }

        return $productReview;
    }
}
