<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidAccountOrPasswordUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-account-or-password';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
