<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\Validation\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class MaxAllowedLimitUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'max-allowed-limit';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
