<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception;

use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class CategoryNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    protected const CODE = 'category-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
