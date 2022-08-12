<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidCredentialsUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'invalid-credentials';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
