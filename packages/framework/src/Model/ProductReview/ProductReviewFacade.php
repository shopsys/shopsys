<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;

class ProductReviewFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductReviewRepository $productReviewRepository,
    ) {
    }

    public function getById(int $productReviewId): ProductReview
    {
        return $this->productReviewRepository->getById($productReviewId);
    }

    public function edit(ProductReview $productReview, ProductReviewData $productReviewData): void
    {
        $productReview->edit($productReviewData);

        $this->em->flush();
    }
}
