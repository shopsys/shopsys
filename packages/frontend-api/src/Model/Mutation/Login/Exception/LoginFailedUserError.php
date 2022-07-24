<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login\Exception;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class LoginFailedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'login-failed';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
