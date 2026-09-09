<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Exception;

use RuntimeException;

class ProductReviewCannotBeApprovedException extends RuntimeException
{
    public function __construct(int $productReviewId)
    {
        parent::__construct(sprintf('Product review with ID %d cannot be approved.', $productReviewId));
    }
}
