<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use GraphQL\Error\UserError;

class InvalidArgumentUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-argument';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
