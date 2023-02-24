<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception;

use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
