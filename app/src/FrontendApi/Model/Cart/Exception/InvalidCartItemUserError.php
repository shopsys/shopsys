<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

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
