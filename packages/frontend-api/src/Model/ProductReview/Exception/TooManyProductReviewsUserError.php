<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TooManyProductReviewsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'too-many-product-reviews';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
