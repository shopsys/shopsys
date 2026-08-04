<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview\Exception;

use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;

class ProductReviewVariantRequiredUserError extends InvalidArgumentUserError
{
    protected const string CODE = 'product-review-variant-required';
}
