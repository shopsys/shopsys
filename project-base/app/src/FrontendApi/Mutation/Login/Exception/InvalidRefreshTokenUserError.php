<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidRefreshTokenUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'invalid-refresh-token';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
