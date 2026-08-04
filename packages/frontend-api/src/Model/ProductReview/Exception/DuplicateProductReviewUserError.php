<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview\Exception;

use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;

class DuplicateProductReviewUserError extends InvalidArgumentUserError
{
    protected const string CODE = 'duplicate-product-review';
}
