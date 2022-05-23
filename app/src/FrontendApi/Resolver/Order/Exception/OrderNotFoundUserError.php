<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order\Exception;

use App\FrontendApi\Error\UserEntityNotFoundError;
use App\FrontendApi\Error\UserErrorWithCodeInterface;

class OrderNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'order-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
