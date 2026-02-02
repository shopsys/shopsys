<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TooManyLoginAttemptsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'too-many-login-attempts';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
