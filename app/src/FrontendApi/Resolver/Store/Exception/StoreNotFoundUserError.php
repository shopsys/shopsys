<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store\Exception;

// TODO-RK vratit az to bude v masteru extends UserEntityNotFoundError implements UserErrorWithCodeInterface
use Overblog\GraphQLBundle\Error\UserError;

class StoreNotFoundUserError extends UserError
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
