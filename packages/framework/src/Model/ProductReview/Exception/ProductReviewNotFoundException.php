<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductReviewNotFoundException extends NotFoundHttpException
{
    public function __construct(int $productReviewId)
    {
        parent::__construct(sprintf('Product review with ID %d not found.', $productReviewId));
    }
}
