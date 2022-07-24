<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use GraphQL\Error\UserError;

class InvalidTokenUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-token';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
