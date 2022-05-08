<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\Exception;

use App\FrontendApi\Error\UserEntityNotFoundError;
use App\FrontendApi\Error\UserErrorWithCodeInterface;

class StoreNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'store-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
