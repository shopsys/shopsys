<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class InvalidCartItemUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'cart-item-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
